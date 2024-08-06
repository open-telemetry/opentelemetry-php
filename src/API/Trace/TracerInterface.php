<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

interface TracerInterface
{
    /** @param non-empty-string $spanName */
    public function spanBuilder(string $spanName): SpanBuilderInterface;

    /**
     * Determine if the tracer is enabled. Instrumentation authors SHOULD call this method prior to
     * creating a new span.
     * @experimental
     */
    public function isEnabled(): bool;
}
