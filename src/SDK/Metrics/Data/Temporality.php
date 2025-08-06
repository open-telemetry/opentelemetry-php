<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

/**
 * Metric aggregation temporality.
 */
enum Temporality: string
{
    case DELTA = 'Delta';
    case CUMULATIVE = 'Cumulative';
}
