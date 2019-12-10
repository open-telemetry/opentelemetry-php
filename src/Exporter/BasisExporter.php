<?php

declare(strict_types=1);

namespace OpenTelemetry\Exporter;

use OpenTelemetry\Exporter;
use OpenTelemetry\Tracing\Span;

class BasisExporter extends Exporter
{
    public function convertSpan(Span $span) : array
    {
        return [
            'traceId' => $span->getContext()->getTraceId(),
            'spanId' => $span->getContext()->getSpanId(),
            'parentSpanId' => $span->getParentContext()
                ? $span->getParentContext()->getSpanId()
                : null,
            'body' => serialize($span),
        ];
    }
}
