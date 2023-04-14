<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

/**
 * @internal
 */
interface MetricCollectorInterface
{
    public function collect(int $timestamp): Metric;
}
