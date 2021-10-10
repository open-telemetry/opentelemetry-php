<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

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
    private int $maxQueueSize;
    private int $scheduledDelayMillis;
    private int $exporterTimeoutMillis;
    private int $maxExportBatchSize;
    private ?int $lastExportTimestamp = null;
    private API\ClockInterface $clock;
    private bool $running = true;

    /** @var list<SpanDataInterface> */
    private array $queue;

    public function __construct(
        ?SpanExporterInterface $exporter,
        API\ClockInterface $clock,
        int $maxQueueSize = 2048,
        int $scheduledDelayMillis = 5000,
        int $exporterTimeoutMillis = 30000,
        int $maxExportBatchSize = 512
    ) {
        if ($maxExportBatchSize > $maxQueueSize) {
            throw new InvalidArgumentException("maxExportBatchSize should be smaller or equal to $maxQueueSize");
        }
        $this->exporter = $exporter;
        $this->clock = $clock;
        $this->maxQueueSize = $maxQueueSize;
        $this->scheduledDelayMillis = $scheduledDelayMillis;
        $this->exporterTimeoutMillis = $exporterTimeoutMillis;
        $this->maxExportBatchSize = $maxExportBatchSize;

        $this->queue = [];
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
        }

        return true;
    }

    /** @inheritDoc */
    public function shutdown(): bool
    {
        if (!$this->running) {
            return true;
        }

        $this->running = false;
        if (null !== $this->exporter) {
            return $this->forceFlush() && $this->exporter->shutdown();
        }

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

        return $this->scheduledDelayMillis < ($now - $this->lastExportTimestamp);
    }
}
