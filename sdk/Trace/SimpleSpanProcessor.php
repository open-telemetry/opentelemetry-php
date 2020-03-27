<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

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
    public function onStart(API\Span $span): void
    {
        // nothing to do here
    }

    /**
     * @inheritDoc
     */
    public function onEnd(API\Span $span): void
    {
        // @todo only spans with SampleFlag === true should be exported according to
        // https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/sdk-tracing.md#sampling
        $this->exporter->export([$span]);
    }

    /**
     * @inheritDoc
     */
    public function shutdown(): void
    {
        // TODO: Implement shutdown() method.
    }
}
