<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

interface Tracer
{
    /** @param non-empty-string $spanName */
    public function spanBuilder(string $spanName): SpanBuilder;
}
