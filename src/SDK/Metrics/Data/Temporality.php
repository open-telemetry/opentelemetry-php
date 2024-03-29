<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

/**
 * Metric aggregation temporality.
 *
 * Has to be type-hinted as `string|Temporality` to be forward compatible.
 * @todo convert to enum (php >= 8.1)
 */
final class Temporality
{
    public const DELTA = 'Delta';
    public const CUMULATIVE = 'Cumulative';

    private function __construct()
    {
    }
}
