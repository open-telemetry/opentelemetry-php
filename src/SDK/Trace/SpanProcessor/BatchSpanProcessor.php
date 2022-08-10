<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use function assert;
use Closure;
use function count;
use function intdiv;
use InvalidArgumentException;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use SplQueue;

class BatchSpanProcessor implements SpanProcessorInterface
{
    public const DEFAULT_SCHEDULE_DELAY = 5000;
    public const DEFAULT_EXPORT_TIMEOUT = 30000;
    public const DEFAULT_MAX_QUEUE_SIZE = 2048;
    public const DEFAULT_MAX_EXPORT_BATCH_SIZE = 512;

    private SpanExporterInterface $exporter;
    private ClockInterface $clock;
    private int $maxQueueSize;
    private int $maxBatchSize;
    private int $scheduledDelayNanos;
    private int $maxExportBatchSize;

    private ?int $nextScheduledRun = null;
    private bool $running = false;
    private int $batchId = 0;
    /** @var list<SpanDataInterface> */
    private array $batch = [];
    /** @var SplQueue<list<SpanDataInterface>> */
    private SplQueue $queue;
    /** @var SplQueue<array{int, Closure}> */
    private SplQueue $flush;

    private bool $closed = false;

    public function __construct(
        SpanExporterInterface $exporter,
        ClockInterface $clock,
        int $maxQueueSize = self::DEFAULT_MAX_QUEUE_SIZE,
        int $scheduledDelayMillis = self::DEFAULT_SCHEDULE_DELAY,
        int $exportTimeoutMillis = self::DEFAULT_EXPORT_TIMEOUT,
        int $maxExportBatchSize = self::DEFAULT_MAX_EXPORT_BATCH_SIZE
    ) {
        if ($maxQueueSize <= 0) {
            throw new InvalidArgumentException(sprintf('Maximum queue size (%d) must be greater than zero', $maxQueueSize));
        }
        if ($scheduledDelayMillis <= 0) {
            throw new InvalidArgumentException(sprintf('Scheduled delay (%d) must be greater than zero', $scheduledDelayMillis));
        }
        if ($exportTimeoutMillis <= 0) {
            throw new InvalidArgumentException(sprintf('Export timeout (%d) must be greater than zero', $exportTimeoutMillis));
        }
        if ($maxExportBatchSize <= 0) {
            throw new InvalidArgumentException(sprintf('Maximum export batch size (%d) must be greater than zero', $maxExportBatchSize));
        }
        if ($maxExportBatchSize > $maxQueueSize) {
            throw new InvalidArgumentException(sprintf('Maximum export batch size (%d) must be less than or equal to maximum queue size (%d)', $maxExportBatchSize, $maxQueueSize));
        }

        $this->exporter = $exporter;
        $this->clock = $clock;
        $this->maxQueueSize = intdiv($maxQueueSize, $maxExportBatchSize);
        $this->maxBatchSize = $maxQueueSize % $maxExportBatchSize;
        $this->scheduledDelayNanos = $scheduledDelayMillis * 1_000_000;
        $this->maxExportBatchSize = $maxExportBatchSize;

        $this->queue = new SplQueue();
        $this->flush = new SplQueue();
    }

    public function onStart(ReadWriteSpanInterface $span, Context $parentContext): void
    {
    }

    public function onEnd(ReadableSpanInterface $span): void
    {
        if ($this->closed) {
            return;
        }
        if (!$span->getContext()->isSampled()) {
            return;
        }

        if (count($this->queue) === $this->maxQueueSize && count($this->batch) === $this->maxBatchSize) {
            return;
        }

        $this->batch[] = $span->toSpanData();
        $this->nextScheduledRun ??= $this->clock->now() + $this->scheduledDelayNanos;

        if (count($this->batch) === $this->maxExportBatchSize) {
            $this->enqueueBatch();
            $this->flush();
        } elseif ($this->clock->now() > $this->nextScheduledRun) {
            $this->flush(static fn () => null);
        }
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        if ($this->closed) {
            return $this->flush->isEmpty();
        }

        return $this->flush(fn (): bool => $this->exporter->forceFlush($cancellation));
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        if ($this->closed) {
            return $this->flush->isEmpty();
        }

        $this->closed = true;

        return $this->flush(fn (): bool => $this->exporter->shutdown($cancellation));
    }

    private function flush(?Closure $forceFlush = null): bool
    {
        if ($forceFlush !== null) {
            $this->flush->enqueue([
                $this->batchId + $this->queue->count() + (int) (bool) $this->batch,
                $forceFlush,
            ]);
        }

        if ($this->running) {
            return false;
        }

        $this->running = true;

        try {
            $this->processFlushTasks();
            while (!$this->queue->isEmpty() || !$this->flush->isEmpty()) {
                if ($this->queue->isEmpty()) {
                    $this->enqueueBatch();
                }
                while (!$this->queue->isEmpty()) {
                    $this->batchId++;
                    $this->exporter->export($this->queue->dequeue())->await();
                    $this->processFlushTasks();
                }
            }
        } finally {
            $this->running = false;
        }

        return true;
    }

    private function enqueueBatch(): void
    {
        assert($this->batch !== []);

        $this->queue->enqueue($this->batch);
        $this->batch = [];
        $this->nextScheduledRun = null;
    }

    private function processFlushTasks(): void
    {
        while (!$this->flush->isEmpty() && $this->flush->bottom()[0] <= $this->batchId) {
            $this->flush->dequeue()[1]();
        }
    }
}
