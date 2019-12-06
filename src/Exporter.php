<?php

declare(strict_types=1);

namespace OpenTelemetry;

use OpenTelemetry\Tracing\Span;
use OpenTelemetry\Tracing\Tracer;

/**
 * A simple Exporter interface
 *
 * @package OpenTelemetry
 */
interface Exporter
{
    /**
     * Possible return values as outlined in the OpenTelemetry spec
     */
    const SUCCESS = 0;
    const FAILED_NOT_RETRYABLE = 1;
    const FAILED_RETRYABLE = 2;

    /**
     * Export trace data (spans)
     * @param Span[] $spans List of spans to export
     * @return int
     */
    public function export(Span ...$spans) : int;

    /**
     * Shutdown the exporter, provide cleanup
     * @return int
     */
    public function shutdown() : int;
}
