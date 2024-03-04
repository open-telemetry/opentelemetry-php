<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Aggregation;

final class SumSummary
{
    public function __construct(public float|int $value)
    {
    }
}
