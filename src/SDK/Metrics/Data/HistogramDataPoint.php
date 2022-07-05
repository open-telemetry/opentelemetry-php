<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Attributes;

final class HistogramDataPoint
{
    /**
     * @readonly
     */
    public int $count;
    /**
     * @var float|int
     * @readonly
     */
    public $sum;
    /**
     * @var float|int
     * @readonly
     */
    public $min;
    /**
     * @var float|int
     * @readonly
     */
    public $max;
    /**
     * @readonly
     */
    public array $bucketCounts;
    /**
     * @readonly
     */
    public array $explicitBounds;
    /**
     * @readonly
     */
    public Attributes $attributes;
    /**
     * @readonly
     */
    public ?int $startTimestamp;
    /**
     * @readonly
     */
    public int $timestamp;
    /**
     * @readonly
     */
    public iterable $exemplars = [];
    /**
     * @param float|int $sum
     * @param float|int $min
     * @param float|int $max
     */
    public function __construct(int $count, $sum, $min, $max, array $bucketCounts, array $explicitBounds, Attributes $attributes, ?int $startTimestamp, int $timestamp, iterable $exemplars = [])
    {
        $this->count = $count;
        $this->sum = $sum;
        $this->min = $min;
        $this->max = $max;
        $this->bucketCounts = $bucketCounts;
        $this->explicitBounds = $explicitBounds;
        $this->attributes = $attributes;
        $this->startTimestamp = $startTimestamp;
        $this->timestamp = $timestamp;
        $this->exemplars = $exemplars;
    }
}
