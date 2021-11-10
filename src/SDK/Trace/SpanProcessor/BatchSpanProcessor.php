<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use Exception;
use InvalidArgumentException;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

class BatchSpanProcessor implements SpanProcessorInterface
{
    private ?SpanExporterInterface $exporter;
    private ?int $maxQueueSize;
    private ?int $scheduledDelayMillis;
    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    /** @phpstan-ignore-next-line */
    private ?int $exporterTimeoutMillis;
    private ?int $maxExportBatchSize;
    private ?int $lastExportTimestamp = null;
    private API\ClockInterface $clock;
    private bool $running = true;

    /** @var list<SpanDataInterface> */
    private array $queue;

    public function __construct(
        ?SpanExporterInterface $exporter,
        API\ClockInterface $clock = null,
        int $maxQueueSize = null,
        int $scheduledDelayMillis = null,
        int $exporterTimeoutMillis = null,
        int $maxExportBatchSize = null
    ) {
        if (null === $clock) {
            $clock = \OpenTelemetry\SDK\Trace\AbstractClock::getDefault();
        }
        $this->exporter = $exporter;
        $this->clock = $clock;
        $this->maxQueueSize = $maxQueueSize ?: $this->fromEnv('OTEL_BSP_MAX_QUEUE_SIZE', 2048);
        $this->scheduledDelayMillis = $scheduledDelayMillis ?: $this->fromEnv('OTEL_BSP_SCHEDULE_DELAY', 5000);
        $this->exporterTimeoutMillis = $exporterTimeoutMillis ?: $this->fromEnv('OTEL_BSP_EXPORT_TIMEOUT', 30000);
        $this->maxExportBatchSize = $maxExportBatchSize ?: $this->fromEnv('OTEL_BSP_MAX_EXPORT_BATCH_SIZE', 512);
        if ($this->maxExportBatchSize > $this->maxQueueSize) {
            throw new InvalidArgumentException("maxExportBatchSize should be smaller or equal to $this->maxQueueSize");
        }

        $this->queue = [];
    }

    private function fromEnv(string $key, int $default): int
    {
        $value = getenv($key);
        if (false === $value) {
            return $default;
        }
        if (!is_numeric($value)) {
            throw new Exception($key . ' is not numeric');
        }

        return (int) $value;
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
        if (!$this->running) {
            return true;
        }

        if (null !== $this->exporter) {
            $this->exporter->export($this->queue);
            $this->queue = [];
            $this->lastExportTimestamp = $this->clock->nanoTime();
            $this->exporter->forceFlush();
        }

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
        $now = $this->clock->nanoTime();

        // if lastExport never occurred let it start from now on
        if (null === $this->lastExportTimestamp) {
            $this->lastExportTimestamp = $now;

            return false;
        }

        return ($this->scheduledDelayMillis * API\ClockInterface::NANOS_PER_MILLISECOND) < ($now - $this->lastExportTimestamp);
    }
}
