<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics;

interface PushMetricExporterInterface extends Metrics\MetricExporterInterface
{
    public function forceFlush(): bool;
}
