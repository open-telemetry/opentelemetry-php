<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use InvalidArgumentException;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Common\Time\StopWatch;
use OpenTelemetry\SDK\Common\Time\StopWatchFactory;
use OpenTelemetry\SDK\Common\Time\Util as TimeUtil;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

class BatchSpanProcessor implements SpanProcessorInterface
{
    use EnvironmentVariablesTrait;

    public const DEFAULT_SCHEDULE_DELAY = 5000;
    public const DEFAULT_EXPORT_TIMEOUT = 30000;
    public const DEFAULT_MAX_QUEUE_SIZE = 2048;
    public const DEFAULT_MAX_EXPORT_BATCH_SIZE = 512;

    private ?SpanExporterInterface $exporter;
    private ?int $maxQueueSize;
    private ?int $scheduledDelayMillis;
    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    /** @phpstan-ignore-next-line */
    private ?int $exporterTimeoutMillis;
    private ?int $maxExportBatchSize;
    private bool $running = true;
    private StopWatch $stopwatch;

    /** @var list<SpanDataInterface> */
    private array $queue = [];

    public function __construct(
        ?SpanExporterInterface $exporter,
        ClockInterface $clock = null,
        int $maxQueueSize = null,
        int $scheduledDelayMillis = null,
        int $exporterTimeoutMillis = null,
        int $maxExportBatchSize = null
    ) {
        $this->exporter = $exporter;
        // @todo make the stopwatch a dependency rather than using the factory?
        $this->stopwatch = StopWatchFactory::create($clock ?? ClockFactory::getDefault())->build();
        $this->stopwatch->start();
        $this->maxQueueSize = $maxQueueSize
            ?: $this->getIntFromEnvironment(Env::OTEL_BSP_MAX_QUEUE_SIZE, self::DEFAULT_MAX_QUEUE_SIZE);
        $this->scheduledDelayMillis = $scheduledDelayMillis
            ?: $this->getIntFromEnvironment(Env::OTEL_BSP_SCHEDULE_DELAY, self::DEFAULT_SCHEDULE_DELAY);
        $this->exporterTimeoutMillis = $exporterTimeoutMillis
            ?: $this->getIntFromEnvironment(Env::OTEL_BSP_EXPORT_TIMEOUT, self::DEFAULT_EXPORT_TIMEOUT);
        $this->maxExportBatchSize = $maxExportBatchSize
            ?: $this->getIntFromEnvironment(Env::OTEL_BSP_MAX_EXPORT_BATCH_SIZE, self::DEFAULT_MAX_EXPORT_BATCH_SIZE);
        if ($this->maxExportBatchSize > $this->maxQueueSize) {
            throw new InvalidArgumentException(
                sprintf('maxExportBatchSize should be smaller or equal to %s', $this->maxQueueSize)
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function onStart(ReadWriteSpanInterface $span, ?Context $parentContext = null): void
    {
    }

    /**
     * @inheritDoc
     */
    public function onEnd(ReadableSpanInterface $span): void
    {
        if (null === $this->exporter) {
            return;
        }

        if (!$this->running) {
            return;
        }

        if ($span->getContext()->isSampled() && !$this->queueReachedLimit()) {
            $this->queue[] = $span->toSpanData();
        }

        if ($this->bufferReachedExportLimit() || $this->enoughTimeHasPassed()) {
            $this->forceFlush();
        }
    }

    /** @inheritDoc */
    public function forceFlush(): bool
    {
        if (!$this->running || $this->exporter === null) {
            return true;
        }

        $this->exporter->export($this->queue);
        $this->queue = [];
        $this->stopwatch->reset();
        $this->exporter->forceFlush();

        return true;
    }

    /** @inheritDoc */
    public function shutdown(): bool
    {
        if (!$this->running) {
            return true;
        }

        if (null !== $this->exporter) {
            $this->forceFlush() && $this->exporter->shutdown();
        }
        $this->running = false;

        return true;
    }

    protected function bufferReachedExportLimit(): bool
    {
        return count($this->queue) >= $this->maxExportBatchSize;
    }

    protected function queueReachedLimit(): bool
    {
        return count($this->queue) >= $this->maxQueueSize;
    }

    protected function enoughTimeHasPassed(): bool
    {
        return TimeUtil::millisToNanos((int) $this->scheduledDelayMillis) < $this->stopwatch->getLastElapsedTime();
    }
}
