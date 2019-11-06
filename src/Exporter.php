<?php

declare(strict_types=1);

namespace OpenTelemetry;

use OpenTelemetry\Tracing\Span;
use OpenTelemetry\Tracing\Tracer;

abstract class Exporter
{
    abstract public function convertSpan(Span $span) : array;

    public function flush(Tracer $tracer, Transport $transport) : int
    {
        $data = [];

        foreach ($tracer->getSpans() as $span) {
            $data[] = $this->convertSpan($span);
        }

        if (count($data)) {
            $transport->write($data);
        }

        return count($data);
    }
}