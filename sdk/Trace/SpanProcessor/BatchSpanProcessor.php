<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace\SpanProcessor;

use InvalidArgumentException;

use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Exporter;
use OpenTelemetry\Sdk\Trace\SpanProcessor;
use OpenTelemetry\Trace as API;

class BatchSpanProcessor implements SpanProcessor
{
    /**
     * @var Exporter|null
     */
    private $exporter;
    /**
     * @var int
     */
    private $maxQueueSize;
    /**
     * @var int
     */
    private $scheduledDelayMillis;
    /**
     * @var int
     */
    private $exporterTimeoutMillis;
    /**
     * @var int
     */
    private $maxExportBatchSize;
    /**
     * @var array
     */
    private $queue;

    /**
     * @var int
     */
    private $lastExportTimestamp = 0;
    /**
     * @var Clock
     */
    private $clock;

    /**
     * @var bool
     */
    private $running = true;

    public function __construct(
        ?Exporter $exporter,
        Clock $clock,
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
    public function onStart(API\Span $span): void
    {
    }

    /**
     * @inheritDoc
     */
    public function onEnd(API\Span $span): void
    {
        if (null !== $this->exporter && $this->running) {
            if (count($this->queue) < $this->maxQueueSize) {
                $this->queue[] = $span;
            }

            if ($this->bufferReachedExportLimit() || $this->enoughTimeHasPassed()) {
                $this->forceFlush();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function forceFlush(): void
    {
        if (null !== $this->exporter && $this->running) {
            $this->exporter->export($this->queue);
            $this->queue = [];
            $this->lastExportTimestamp = $this->clock->timestamp();
        }
    }

    protected function bufferReachedExportLimit(): bool
    {
        return count($this->queue) >= $this->maxExportBatchSize;
    }

    protected function enoughTimeHasPassed(): bool
    {
        $now = $this->clock->timestamp();

        return $this->scheduledDelayMillis < ($now - $this->lastExportTimestamp);
    }

    /**
     * @inheritDoc
     */
    public function shutdown(): void
    {
        $this->running = false;
        if (null !== $this->exporter) {
            $this->forceFlush();
            $this->exporter->shutdown();
        }
    }
}
