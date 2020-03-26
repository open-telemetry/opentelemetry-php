<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use InvalidArgumentException;

use OpenTelemetry\Sdk\Internal\Clock;
use OpenTelemetry\Trace as API;

class BatchSpanProcessor implements SpanProcessor
{
    /**
     * @var Exporter
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

    public function __construct(
        Exporter $exporter,
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
        if (count($this->queue) < $this->maxQueueSize) {
            $this->queue[] = $span;
        }

        if ($this->bufferReachedExportLimit() && $this->enoughTimeHasPassed()) {
            $this->exporter->export($this->queue);
            $this->queue = [];
            $this->lastExportTimestamp = (int) \round((float) $this->clock->millitime());
        }
    }

    protected function bufferReachedExportLimit(): bool
    {
        return count($this->queue) >= $this->maxExportBatchSize;
    }

    protected function enoughTimeHasPassed(): bool
    {
        $now = (int) \round((float) $this->clock->millitime());

        return $this->scheduledDelayMillis < ($now - $this->lastExportTimestamp);
    }

    /**
     * @inheritDoc
     */
    public function shutdown(): void
    {
        // TODO: Implement shutdown() method.
    }
}
