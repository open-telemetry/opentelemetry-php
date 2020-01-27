<?php

namespace OpenTelemetry\Trace\SpanProcessor;

use InvalidArgumentException;
use OpenTelemetry\Exporter\ExporterInterface;
use OpenTelemetry\Internal\Clock;
use OpenTelemetry\Trace\Span;

class BatchSpanProcessor implements SpanProcessorInterface
{
    /**
     * @var ExporterInterface
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
        ExporterInterface $exporter,
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
    public function onStart(Span $span): void
    {
    }

    /**
     * @inheritDoc
     */
    public function onEnd(Span $span): void
    {
        if (count($this->queue) < $this->maxQueueSize ) {
            $this->queue[] = $span;
        }

        if ($this->bufferReachedExportLimit() && $this->enoughTimeHasPassed()) {
            $this->exporter->export($this->queue);
            $this->queue = [];
            $this->lastExportTimestamp = $this->clock->millitime();
        }
    }

    protected function bufferReachedExportLimit(): bool
    {
        return count($this->queue) >= $this->maxExportBatchSize;
    }

    protected function enoughTimeHasPassed(): bool
    {
        return $this->scheduledDelayMillis < ($this->clock->millitime() - $this->lastExportTimestamp);
    }
}