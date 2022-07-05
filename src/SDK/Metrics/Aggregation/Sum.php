<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Aggregation;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\Metrics\Aggregation;
use OpenTelemetry\SDK\Metrics\Data;
use OpenTelemetry\SDK\Metrics\Data\Temporality;

/**
 * @implements Aggregation<SumSummary>
 */
final class Sum implements Aggregation
{
    private bool $monotonic;

    public function __construct(bool $monotonic = false)
    {
        $this->monotonic = $monotonic;
    }

    public function initialize(): SumSummary
    {
        return new SumSummary(0);
    }

    /**
     * @param SumSummary $summary
     */
    public function record(mixed $summary, float|int $value, Attributes $attributes, Context $context, int $timestamp): void
    {
        $summary->value += $value;
    }

    /**
     * @param SumSummary $left
     * @param SumSummary $right
     */
    public function merge(mixed $left, mixed $right): SumSummary
    {
        $sum = $left->value + $right->value;

        return new SumSummary(
            $sum,
        );
    }

    /**
     * @param SumSummary $left
     * @param SumSummary $right
     */
    public function diff(mixed $left, mixed $right): SumSummary
    {
        $sum = -$left->value + $right->value;

        return new SumSummary(
            $sum,
        );
    }

    /**
     * @param array<SumSummary> $summaries
     */
    public function toData(
        array $attributes,
        array $summaries,
        array $exemplars,
        ?int $startTimestamp,
        int $timestamp,
        Temporality $temporality,
    ): Data\Sum {
        $dataPoints = [];
        foreach ($attributes as $key => $dataPointAttributes) {
            $dataPoints[] = new Data\NumberDataPoint(
                $summaries[$key]->value,
                $dataPointAttributes,
                $startTimestamp,
                $timestamp,
                $exemplars[$key] ?? [],
            );
        }

        return new Data\Sum(
            $dataPoints,
            $temporality,
            $this->monotonic,
        );
    }
}
