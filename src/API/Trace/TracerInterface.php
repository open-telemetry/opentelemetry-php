<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

/**
 * Implementations MUST be safe for concurrent use, for example from multiple fibers.
 */
interface TracerInterface
{
    /** @param non-empty-string $spanName */
    public function spanBuilder(string $spanName): SpanBuilderInterface;

    /**
     * Determine if the tracer is enabled. Instrumentation authors SHOULD call this method prior to
     * creating a new span.
     */
    public function isEnabled(): bool;
}
