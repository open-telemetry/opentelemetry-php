<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs\Processor;

use InvalidArgumentException;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\API\Metrics\UpDownCounterInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\ReadWriteLogRecord;
use OpenTelemetry\SemConv\Incubating\Attributes\OtelIncubatingAttributes;
use OpenTelemetry\SemConv\Incubating\Metrics\OtelIncubatingMetrics;
use OpenTelemetry\SemConv\Version;
use SplQueue;
use Throwable;

class BatchLogRecordProcessor implements LogRecordProcessorInterface
{
    use LogsMessagesTrait;

    public const DEFAULT_SCHEDULE_DELAY = 1000;
    public const DEFAULT_EXPORT_TIMEOUT = 30000;
    public const DEFAULT_MAX_QUEUE_SIZE = 2048;
    public const DEFAULT_MAX_EXPORT_BATCH_SIZE = 512;

    private int $maxQueueSize;
    private int $scheduledDelayNanos;
    private int $maxExportBatchSize;
    private ContextInterface $exportContext;

    private ?int $nextScheduledRun = null;
    private bool $running = false;
    private int $batchId = 0;
    private int $queueSize = 0;
    /** @var list<ReadWriteLogRecord> */
    private array $batch = [];
    /** @var SplQueue<list<ReadWriteLogRecord>> */
    private SplQueue $queue;
    /** @var SplQueue<array{int, string, ?CancellationInterface, bool, ContextInterface}> */
    private SplQueue $flush;

    private bool $closed = false;

    private readonly ?CounterInterface $logProcessedCounter;
    private readonly ?UpDownCounterInterface $logInflightCounter;
    private readonly ?CounterInterface $logExportedCounter;

    /** @var array<non-empty-string, string> */
    private readonly array $processorAttributes;
    /** @var array<non-empty-string, string> */
    private readonly array $exporterAttributes;

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

        $this->processorAttributes = [
            OtelIncubatingAttributes::OTEL_COMPONENT_TYPE => OtelIncubatingAttributes::OTEL_COMPONENT_TYPE_VALUE_BATCHING_LOG_PROCESSOR,
            OtelIncubatingAttributes::OTEL_COMPONENT_NAME => OtelIncubatingAttributes::OTEL_COMPONENT_TYPE_VALUE_BATCHING_LOG_PROCESSOR . '/' . spl_object_id($this),
        ];

        if ($meterProvider === null) {
            $this->exporterAttributes = [];
            $this->logProcessedCounter = null;
            $this->logInflightCounter = null;
            $this->logExportedCounter = null;
        } else {
            $this->exporterAttributes = [
                OtelIncubatingAttributes::OTEL_COMPONENT_NAME => (new \ReflectionClass($this->exporter))->getShortName(),
            ];

            $meter = $meterProvider->getMeter('io.opentelemetry.sdk', schemaUrl: Version::VERSION_1_36_0->url());
            $meter
                ->createObservableUpDownCounter(
                    OtelIncubatingMetrics::OTEL_SDK_PROCESSOR_LOG_QUEUE_CAPACITY,
                    '{log_record}',
                    'The maximum number of log records the queue of a given log record processor can hold',
                )
                ->observe(function (ObserverInterface $observer): void {
                    $observer->observe($this->maxQueueSize, $this->processorAttributes);
                });
            $meter
                ->createObservableUpDownCounter(
                    OtelIncubatingMetrics::OTEL_SDK_PROCESSOR_LOG_QUEUE_SIZE,
                    '{log_record}',
                    'The number of log records in the queue of a given log record processor',
                )
                ->observe(function (ObserverInterface $observer): void {
                    $observer->observe($this->queueSize, $this->processorAttributes);
                });
            $this->logProcessedCounter = $meter->createCounter(
                OtelIncubatingMetrics::OTEL_SDK_PROCESSOR_LOG_PROCESSED,
                '{log_record}',
                'The number of log records for which the processing has finished, either successful or failed',
            );
            $this->logInflightCounter = $meter->createUpDownCounter(
                OtelIncubatingMetrics::OTEL_SDK_EXPORTER_LOG_INFLIGHT,
                '{log_record}',
                'The number of log records which were passed to the exporter, but that have not been exported yet',
            );
            $this->logExportedCounter = $meter->createCounter(
                OtelIncubatingMetrics::OTEL_SDK_EXPORTER_LOG_EXPORTED,
                '{log_record}',
                'The number of log records for which the export has finished, either successful or failed',
            );
        }
    }

    #[\Override]
    public function onEmit(ReadWriteLogRecord $record, ?ContextInterface $context = null): void
    {
        if ($this->closed) {
            return;
        }

        if ($this->queueSize === $this->maxQueueSize) {
            $this->logProcessedCounter?->add(1, $this->processorAttributes + ['error.type' => '_OTHER']);

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

    #[\Override]
    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        if ($this->closed) {
            return false;
        }

        return $this->flush(__FUNCTION__, $cancellation);
    }

    #[\Override]
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
                $this->logInflightCounter?->add($batchSize, $this->exporterAttributes);

                try {
                    $this->exporter->export($this->queue->dequeue())->await();
                    $this->logExportedCounter?->add($batchSize, $this->exporterAttributes);
                    $this->logProcessedCounter?->add($batchSize, $this->processorAttributes);
                } catch (Throwable $e) {
                    $errorAttrs = ['error.type' => $e::class];
                    $this->logExportedCounter?->add($batchSize, $this->exporterAttributes + $errorAttrs);
                    $this->logProcessedCounter?->add($batchSize, $this->processorAttributes + $errorAttrs);
                    self::logError('Unhandled export error', ['exception' => $e]);
                } finally {
                    $this->queueSize -= $batchSize;
                    $this->logInflightCounter?->add(-$batchSize, $this->exporterAttributes);
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
