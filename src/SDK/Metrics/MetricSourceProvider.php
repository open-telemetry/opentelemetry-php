<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Temporality;

interface MetricSourceProvider
{
    /**
     * @param string|Temporality $temporality
     */
    public function create($temporality): MetricSource;
}
