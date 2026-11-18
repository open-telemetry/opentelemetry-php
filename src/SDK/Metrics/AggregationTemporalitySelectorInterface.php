<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Temporality;

interface AggregationTemporalitySelectorInterface
{
    /**
     * Returns the temporality to use for the given metric.
     *
     * Return {@see Temporality::CUMULATIVE} if the exporter does not require a
     * specific temporality.
     *
     * @return string|Temporality|null temporality to use, or null to signal
     *         that the given metric should not be exported by this exporter
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/metrics/sdk.md#metricreader
     */
    public function temporality(MetricMetadataInterface $metric);
}
