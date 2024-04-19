<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs\Processor;

use InvalidArgumentException;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\ReadWriteLogRecord;
use SplQueue;
use Throwable;

class BatchLogRecordProcessor implements LogRecordProcessorInterface
{
    use LogsMessagesTrait;

    public const DEFAULT_SCHEDULE_DELAY = 1000;
    public const DEFAULT_EXPORT_TIMEOUT = 30000;
    public const DEFAULT_MAX_QUEUE_SIZE = 2048;
    public const DEFAULT_MAX_EXPORT_BATCH_SIZE = 512;

    private const ATTRIBUTES_PROCESSOR = ['processor' => 'batching'];
    private const ATTRIBUTES_QUEUED    = self::ATTRIBUTES_PROCESSOR + ['state' => 'queued'];
    private const ATTRIBUTES_PENDING   = self::ATTRIBUTES_PROCESSOR + ['state' => 'pending'];
    private const ATTRIBUTES_PROCESSED = self::ATTRIBUTES_PROCESSOR + ['state' => 'processed'];
    private const ATTRIBUTES_DROPPED   = self::ATTRIBUTES_PROCESSOR + ['state' => 'dropped'];
    private const ATTRIBUTES_FREE      = self::ATTRIBUTES_PROCESSOR + ['state' => 'free'];
    private int $maxQueueSize;
    private int $scheduledDelayNanos;
    private int $maxExportBatchSize;
    private ContextInterface $exportContext;

    private ?int $nextScheduledRun = null;
    private bool $running = false;
    private int $dropped = 0;
    private int $processed = 0;
    private int $batchId = 0;
    private int $queueSize = 0;
    /** @var list<ReadWriteLogRecord> */
    private array $batch = [];
    /** @var SplQueue<list<ReadWriteLogRecord>> */
    private SplQueue $queue;
    /** @var SplQueue<array{int, string, ?CancellationInterface, bool, ContextInterface}> */
    private SplQueue $flush;

    private bool $closed = false;

    public function __construct(
        private readonly LogRecordExporterInterface $exporter,
        private readonly ClockInterface $clock,
        int $maxQueueSize = self::DEFAULT_MAX_QUEUE_SIZE,
        int $scheduledDelayMillis = self::DEFAULT_SCHEDULE_DELAY,
        int $exportTimeoutMillis = self::DEFAULT_EXPORT_TIMEOUT,
        int $maxExportBatchSize = self::DEFAULT_MAX_EXPORT_BATCH_SIZE,
        private readonly bool $autoFlush = true,
        ?MeterProviderInterface $meterProvider = null,
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
        $this->maxQueueSize = $maxQueueSize;
        $this->scheduledDelayNanos = $scheduledDelayMillis * 1_000_000;
        $this->maxExportBatchSize = $maxExportBatchSize;

        $this->exportContext = Context::getCurrent();
        $this->queue = new SplQueue();
        $this->flush = new SplQueue();

        if ($meterProvider === null) {
            return;
        }

        $meter = $meterProvider->getMeter('io.opentelemetry.sdk');
        $meter
            ->createObservableUpDownCounter(
                'otel.logs.log_processor.logs',
                '{logs}',
                'The number of log records received by the processor',
            )
            ->observe(function (ObserverInterface $observer): void {
                $queued = $this->queue->count() * $this->maxExportBatchSize + count($this->batch);
                $pending = $this->queueSize - $queued;
                $processed = $this->processed;
                $dropped = $this->dropped;

                $observer->observe($queued, self::ATTRIBUTES_QUEUED);
                $observer->observe($pending, self::ATTRIBUTES_PENDING);
                $observer->observe($processed, self::ATTRIBUTES_PROCESSED);
                $observer->observe($dropped, self::ATTRIBUTES_DROPPED);
            });
        $meter
            ->createObservableUpDownCounter(
                'otel.logs.log_processor.queue.limit',
                '{logs}',
                'The queue size limit',
            )
            ->observe(function (ObserverInterface $observer): void {
                $observer->observe($this->maxQueueSize, self::ATTRIBUTES_PROCESSOR);
            });
        $meter
            ->createObservableUpDownCounter(
                'otel.logs.log_processor.queue.usage',
                '{logs}',
                'The current queue usage',
            )
            ->observe(function (ObserverInterface $observer): void {
                $queued = $this->queue->count() * $this->maxExportBatchSize + count($this->batch);
                $pending = $this->queueSize - $queued;
                $free = $this->maxQueueSize - $this->queueSize;

                $observer->observe($queued, self::ATTRIBUTES_QUEUED);
                $observer->observe($pending, self::ATTRIBUTES_PENDING);
                $observer->observe($free, self::ATTRIBUTES_FREE);
            });
    }

    public function onEmit(ReadWriteLogRecord $record, ?ContextInterface $context = null): void
    {
        if ($this->closed) {
            return;
        }

        if ($this->queueSize === $this->maxQueueSize) {
            $this->dropped++;

            return;
        }

        $this->queueSize++;
        $this->batch[] = $record;
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
            $this->flush->enqueue([$flushId, $flushMethod, $cancellation, !$this->running, Context::getCurrent()]);
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
                    [, $flushMethod, $cancellation, $propagateResult, $context] = $this->flush->dequeue();
                    $scope = $context->activate();

                    try {
                        $result = $this->exporter->$flushMethod($cancellation);
                        if ($propagateResult) {
                            $success = $result;
                        }
                    } catch (Throwable $e) {
                        if ($propagateResult) {
                            $exception = $e;
                        } else {
                            self::logError(sprintf('Unhandled %s error', $flushMethod), ['exception' => $e]);
                        }
                    } finally {
                        $scope->detach();
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
                $scope = $this->exportContext->activate();

                try {
                    $this->exporter->export($this->queue->dequeue())->await();
                } catch (Throwable $e) {
                    self::logError('Unhandled export error', ['exception' => $e]);
                } finally {
                    $this->processed += $batchSize;
                    $this->queueSize -= $batchSize;
                    $scope->detach();
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
