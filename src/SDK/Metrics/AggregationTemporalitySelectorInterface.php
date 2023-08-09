<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Temporality;

interface AggregationTemporalitySelectorInterface
{
    /**
     * Returns the temporality to use for the given metric.
     *
     * It is recommended to return {@see MetricMetadataInterface::temporality()}
     * if the exporter does not require a specific temporality.
     *
     * @return string|Temporality|null temporality to use, or null to signal
     *         that the given metric should not be exported by this exporter
     */
    public function temporality(MetricMetadataInterface $metric);
}
