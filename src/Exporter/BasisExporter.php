<?php

declare(strict_types=1);

namespace OpenTelemetry\Exporter;

use OpenTelemetry\Trace\Span;

class BasisExporter implements ExporterInterface
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

    public function export(iterable $spans): int
    {
        return Status::SUCCESS;
    }
}
