<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use function assert;
use function count;
use InvalidArgumentException;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopCounter;
use OpenTelemetry\API\Metrics\Noop\NoopUpDownCounter;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\API\Metrics\UpDownCounterInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use SplQueue;
use function sprintf;
use Throwable;

class BatchSpanProcessor implements SpanProcessorInterface
{
    use LogsMessagesTrait;

    public const DEFAULT_SCHEDULE_DELAY = 5000;
    public const DEFAULT_EXPORT_TIMEOUT = 30000;
    public const DEFAULT_MAX_QUEUE_SIZE = 2048;
    public const DEFAULT_MAX_EXPORT_BATCH_SIZE = 512;

    private static int $instanceCount = 0;

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
    /** @var list<SpanDataInterface> */
    private array $batch = [];
    /** @var SplQueue<list<SpanDataInterface>> */
    private SplQueue $queue;
    /** @var SplQueue<array{int, string, ?CancellationInterface, bool, ContextInterface}> */
    private SplQueue $flush;

    private bool $closed = false;

    private readonly CounterInterface $spanProcessedCounter;
    private readonly UpDownCounterInterface $spanInflightCounter;
    private readonly CounterInterface $spanExportedCounter;

    /** @var array<string, string> */
    private readonly array $processorAttributes;
    /** @var array<string, string> */
    private readonly array $exporterAttributes;

    public function __construct(
        private readonly SpanExporterInterface $exporter,
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

        $instanceId = self::$instanceCount++;
        $this->processorAttributes = [
            'otel.component.type' => 'batching_span_processor',
            'otel.component.name' => 'batching_span_processor/' . $instanceId,
        ];
        $exporterClass = (new \ReflectionClass($this->exporter))->getShortName();
        $this->exporterAttributes = [
            'otel.component.name' => $exporterClass,
        ];

        if ($meterProvider === null) {
            $this->spanProcessedCounter = new NoopCounter();
            $this->spanInflightCounter = new NoopUpDownCounter();
            $this->spanExportedCounter = new NoopCounter();

            return;
        }

        $meter = $meterProvider->getMeter('io.opentelemetry.sdk');
        $meter
            ->createObservableUpDownCounter(
                'otel.sdk.processor.span.queue.capacity',
                '{span}',
                'The maximum number of spans the queue of a given span processor can hold',
            )
            ->observe(function (ObserverInterface $observer): void {
                $observer->observe($this->maxQueueSize, $this->processorAttributes);
            });
        $meter
            ->createObservableUpDownCounter(
                'otel.sdk.processor.span.queue.size',
                '{span}',
                'The number of spans in the queue of a given span processor',
            )
            ->observe(function (ObserverInterface $observer): void {
                $observer->observe($this->queueSize, $this->processorAttributes);
            });
        $this->spanProcessedCounter = $meter->createCounter(
            'otel.sdk.processor.span.processed',
            '{span}',
            'The number of spans for which the processing has finished, either successful or failed',
        );
        $this->spanInflightCounter = $meter->createUpDownCounter(
            'otel.sdk.exporter.span.inflight',
            '{span}',
            'The number of spans which were passed to the exporter, but that have not been exported yet',
        );
        $this->spanExportedCounter = $meter->createCounter(
            'otel.sdk.exporter.span.exported',
            '{span}',
            'The number of spans for which the export has finished, either successful or failed',
        );
    }

    #[\Override]
    public function onStart(ReadWriteSpanInterface $span, ContextInterface $parentContext): void
    {
    }

    #[\Override]
    public function onEnd(ReadableSpanInterface $span): void
    {
        if ($this->closed) {
            return;
        }
        if (!$span->getContext()->isSampled()) {
            return;
        }

        if ($this->queueSize === $this->maxQueueSize) {
            $this->dropped++;

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

    public static function builder(SpanExporterInterface $exporter): BatchSpanProcessorBuilder
    {
        return new BatchSpanProcessorBuilder($exporter);
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
                $this->spanInflightCounter->add($batchSize, $this->exporterAttributes);

                try {
                    $this->exporter->export($this->queue->dequeue())->await();
                    $this->spanExportedCounter->add($batchSize, $this->exporterAttributes);
                    $this->spanProcessedCounter->add($batchSize, $this->processorAttributes);
                } catch (Throwable $e) {
                    $errorAttrs = ['error.type' => $e::class];
                    $this->spanExportedCounter->add($batchSize, $this->exporterAttributes + $errorAttrs);
                    $this->spanProcessedCounter->add($batchSize, $this->processorAttributes + $errorAttrs);
                    self::logError('Unhandled export error', ['exception' => $e]);
                } finally {
                    $this->processed += $batchSize;
                    $this->queueSize -= $batchSize;
                    $this->spanInflightCounter->add(-$batchSize, $this->exporterAttributes);
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
