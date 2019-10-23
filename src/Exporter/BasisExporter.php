<?php

declare(strict_types=1);

namespace OpenTelemetry\Exporter;

use OpenTelemetry\Exporter;
use OpenTelemetry\Tracing\Span;
use OpenTelemetry\Tracing\Tracer;

class BasisExporter extends Exporter
{
    public function convertSpan(Span $span) : array
    {
        return [
            'traceId' => $span->getSpanContext()->getTraceId(),
            'spanId' => $span->getSpanContext()->getSpanId(),
            'parentSpanId' => $span->getParentSpanContext() 
                ? $span->getParentSpanContext()->getSpanId()
                : null,
            'body' => serialize($span),
        ];
    }
}
