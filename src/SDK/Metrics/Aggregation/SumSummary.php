<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Aggregation;

final class SumSummary
{
    /**
     * @var float|int
     */
    public $value;
    /**
     * @param float|int $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }
}
