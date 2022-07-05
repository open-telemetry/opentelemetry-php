<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Aggregation;

final class LastValueSummary
{
    public function __construct(
        public float|int|null $value,
        public int $timestamp,
    ) {
    }
}
