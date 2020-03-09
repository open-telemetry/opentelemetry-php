<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace\SpanProcessor;

use OpenTelemetry\Exporter\Exporter;
use OpenTelemetry\Trace\Span;

class SimpleSpanProcessor implements SpanProcessor
{
    /**
     * @var Exporter
     */
    private $exporter;

    public function __construct(Exporter $exporter)
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
