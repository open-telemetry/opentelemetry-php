<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

/**
 * TODO deprecated
 */
interface MetricExporterFactoryInterface
{
    public function create(): MetricExporterInterface;
}
