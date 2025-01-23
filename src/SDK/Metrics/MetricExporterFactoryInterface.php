<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Common\Services\SpiLoadableInterface;

interface MetricExporterFactoryInterface extends SpiLoadableInterface
{
    public function create(): MetricExporterInterface;
}
