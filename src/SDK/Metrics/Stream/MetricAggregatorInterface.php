<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

/**
 * @internal
 */
interface MetricAggregatorInterface extends WritableMetricStreamInterface, MetricCollectorInterface
{
}
