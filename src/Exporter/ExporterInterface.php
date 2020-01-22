<?php

declare(strict_types=1);

namespace OpenTelemetry\Exporter;

use OpenTelemetry\Trace\Span;

/**
 * A simple Exporter interface
 *
 * @package OpenTelemetry
 */
interface ExporterInterface
{
    /**
     * Export trace data (spans)
     * @param iterable<Span> $spans Batch of spans to export
     * @return int Result of operation
     * @see \OpenTelemetry\Exporter\Status
     */
    public function export(iterable $spans) : int;

    /* The spec mentions a shutdown() function. We don't see this as necessary;
     * if an Exporter needs to clean up, it can use a destructor.
     */
}
