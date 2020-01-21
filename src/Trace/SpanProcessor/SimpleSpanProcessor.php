<?php

namespace OpenTelemetry\Trace\SpanProcessor;

use OpenTelemetry\Exporter\ExporterInterface;
use OpenTelemetry\Trace\Span;

class SimpleSpanProcessor implements SpanProcessorInterface
{
    /**
     * @var ExporterInterface
     */
    private $exporter;

    public function __construct(ExporterInterface $exporter)
    {
        $this->exporter = $exporter;
    }

    /**
     * @inheritDoc
     */
    public function onStart(Span $span): void
    {
        // nothing to do here
    }

    /**
     * @inheritDoc
     */
    public function onEnd(Span $span): void
    {
        // @todo only spans with SampleFlag === true should be exported according to
        // https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/sdk-tracing.md#sampling
        $this->exporter->export([$span]);
    }
}