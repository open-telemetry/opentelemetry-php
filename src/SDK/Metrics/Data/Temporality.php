<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

/**
 * Metric aggregation temporality.
 */
enum Temporality
{
    case DELTA;
    case CUMULATIVE;
}
