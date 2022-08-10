<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use function assert;
use function count;
use InvalidArgumentException;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SplQueue;
use function sprintf;
use Throwable;

class BatchSpanProcessor implements SpanProcessorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const DEFAULT_SCHEDULE_DELAY = 5000;
    public const DEFAULT_EXPORT_TIMEOUT = 30000;
    public const DEFAULT_MAX_QUEUE_SIZE = 2048;
    public const DEFAULT_MAX_EXPORT_BATCH_SIZE = 512;

    private SpanExporterInterface $exporter;
    private ClockInterface $clock;
    private int $maxQueueSize;
    private int $scheduledDelayNanos;
    private int $maxExportBatchSize;
    private bool $autoFlush;

    private ?int $nextScheduledRun = null;
    private bool $running = false;
    private int $batchId = 0;
    private int $queueSize = 0;
    /** @var list<SpanDataInterface> */
    private array $batch = [];
    /** @var SplQueue<list<SpanDataInterface>> */
    private SplQueue $queue;
    /** @var SplQueue<array{int, string, ?CancellationInterface, bool}> */
    private SplQueue $flush;

    private bool $closed = false;

    public function __construct(
        SpanExporterInterface $exporter,
        ClockInterface $clock,
        int $maxQueueSize = self::DEFAULT_MAX_QUEUE_SIZE,
        int $scheduledDelayMillis = self::DEFAULT_SCHEDULE_DELAY,
        int $exportTimeoutMillis = self::DEFAULT_EXPORT_TIMEOUT,
        int $maxExportBatchSize = self::DEFAULT_MAX_EXPORT_BATCH_SIZE,
        bool $autoFlush = true
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
        $this->maxQueueSize = $maxQueueSize;
        $this->scheduledDelayNanos = $scheduledDelayMillis * 1_000_000;
        $this->maxExportBatchSize = $maxExportBatchSize;
        $this->autoFlush = $autoFlush;

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

        if ($this->queueSize === $this->maxQueueSize) {
            return;
        }

        $this->queueSize++;
        $this->batch[] = $span->toSpanData();
        $this->nextScheduledRun ??= $this->clock->now() + $this->scheduledDelayNanos;

        if (count($this->batch) === $this->maxExportBatchSize) {
            $this->enqueueBatch();
        }
        if ($this->autoFlush) {
            $this->flush();
        }
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        if ($this->closed) {
            return false;
        }

        return $this->flush(__FUNCTION__, $cancellation);
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;

        return $this->flush(__FUNCTION__, $cancellation);
    }

    private function flush(?string $flushMethod = null, ?CancellationInterface $cancellation = null): bool
    {
        if ($flushMethod !== null) {
            $flushId = $this->batchId + $this->queue->count() + (int) (bool) $this->batch;
            $this->flush->enqueue([$flushId, $flushMethod, $cancellation, !$this->running]);
        }

        if ($this->running) {
            return false;
        }

        $success = true;
        $exception = null;
        $this->running = true;

        try {
            for (;;) {
                while (!$this->flush->isEmpty() && $this->flush->bottom()[0] <= $this->batchId) {
                    [, $flushMethod, $cancellation, $propagateResult] = $this->flush->dequeue();

                    try {
                        $result = $this->exporter->$flushMethod($cancellation);
                        if ($propagateResult) {
                            $success = $result;
                        }
                    } catch (Throwable $e) {
                        if ($propagateResult) {
                            $exception = $e;

                            continue;
                        }
                        if ($this->logger !== null) {
                            $this->logger->error(sprintf('Unhandled %s error', $flushMethod), ['exception' => $e]);
                        }
                    }
                }

                if (!$this->shouldFlush()) {
                    break;
                }

                if ($this->queue->isEmpty()) {
                    $this->enqueueBatch();
                }
                $batchSize = count($this->queue->bottom());
                $this->batchId++;

                try {
                    $this->exporter->export($this->queue->dequeue())->await();
                } catch (Throwable $e) {
                    if ($this->logger !== null) {
                        $this->logger->error('Unhandled export error', ['exception' => $e]);
                    }
                } finally {
                    $this->queueSize -= $batchSize;
                }
            }
        } finally {
            $this->running = false;
        }

        if ($exception !== null) {
            throw $exception;
        }

        return $success;
    }

    private function shouldFlush(): bool
    {
        return !$this->flush->isEmpty()
            || $this->autoFlush && !$this->queue->isEmpty()
            || $this->autoFlush && $this->nextScheduledRun !== null && $this->clock->now() > $this->nextScheduledRun;
    }

    private function enqueueBatch(): void
    {
        assert($this->batch !== []);

        $this->queue->enqueue($this->batch);
        $this->batch = [];
        $this->nextScheduledRun = null;
    }
}
