<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Aggregation;

final class LastValueSummary
{
    /**
     * @var float|int|null
     */
    public $value;
    public int $timestamp;
    /**
     * @param float|int|null $value
     */
    public function __construct($value, int $timestamp)
    {
        $this->value = $value;
        $this->timestamp = $timestamp;
    }
}
