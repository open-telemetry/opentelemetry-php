<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

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
     * @param iterable<API\Span> $spans Batch of spans to export
     * @return int
     */
    public function export(iterable $spans): int;

    public function shutdown(): void;
}
