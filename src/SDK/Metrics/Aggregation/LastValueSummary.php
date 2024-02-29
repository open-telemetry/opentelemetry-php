<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Aggregation;

final class LastValueSummary
{
    /**
     * @param float|int|null $value
     */
    public function __construct(public $value, public int $timestamp)
    {
    }
}
