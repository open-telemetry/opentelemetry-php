<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface Exporter
{
    // todo: Is that OKAY? Just copied this from SpanExporter
    /**
     * Possible return values as outlined in the OpenTelemetry spec
     */
    const SUCCESS = 0;
    const FAILED_NOT_RETRYABLE = 1;
    const FAILED_RETRYABLE = 2;

    /**
     * export.
     *
     * @access	public
     * @param	iterable<Metric> $metrics
     * @return	void
     * 
     * todo Should we pass a result callback in the 2nd parameter?
     */
    public function export(iterable $metrics): void;
}
