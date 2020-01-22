<?php

namespace OpenTelemetry\Trace\SpanProcessor;

use InvalidArgumentException;
use OpenTelemetry\Exporter\ExporterInterface;
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

    public function __construct(
        ExporterInterface $exporter,
        int $maxQueueSize = 2048,
        int $scheduledDelayMillis = 5000,
        int $exporterTimeoutMillis = 30000,
        int $maxExportBatchSize = 512
    ) {
        if ($maxExportBatchSize > $maxQueueSize) {
            throw new InvalidArgumentException("maxExportBatchSize should be smaller or equal to $maxQueueSize");
        }
        $this->exporter = $exporter;
        $this->maxQueueSize = $maxQueueSize;
        $this->scheduledDelayMillis = $scheduledDelayMillis;
        $this->exporterTimeoutMillis = $exporterTimeoutMillis;
        $this->maxExportBatchSize = $maxExportBatchSize;
    }

    /**
     * @inheritDoc
     */
    public function onStart(Span $span): void
    {
        // TODO: Implement onStart() method.
    }

    /**
     * @inheritDoc
     */
    public function onEnd(Span $span): void
    {
        // TODO: Implement onEnd() method.
    }
}