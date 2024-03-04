<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Aggregation;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\Data;

/**
 * @implements AggregationInterface<LastValueSummary>
 */
final class LastValueAggregation implements AggregationInterface
{
    public function initialize(): LastValueSummary
    {
        return new LastValueSummary(null, 0);
    }

    /**
     * @param LastValueSummary $summary
     */
    public function record($summary, $value, AttributesInterface $attributes, ContextInterface $context, int $timestamp): void
    {
        if ($summary->value === null || $timestamp >= $summary->timestamp) {
            $summary->value = $value;
            $summary->timestamp = $timestamp;
        }
    }

    /**
     * @param LastValueSummary $left
     * @param LastValueSummary $right
     */
    public function merge($left, $right): LastValueSummary
    {
        return $right->timestamp >= $left->timestamp ? $right : $left;
    }

    /**
     * @param LastValueSummary $left
     * @param LastValueSummary $right
     */
    public function diff($left, $right): LastValueSummary
    {
        return $right->timestamp >= $left->timestamp ? $right : $left;
    }

    /**
     * @param array<LastValueSummary> $summaries
     */
    public function toData(
        array $attributes,
        array $summaries,
        array $exemplars,
        int $startTimestamp,
        int $timestamp,
        $temporality,
    ): Data\Gauge {
        $dataPoints = [];
        foreach ($attributes as $key => $dataPointAttributes) {
            if ($summaries[$key]->value === null) {
                continue;
            }

            $dataPoints[] = new Data\NumberDataPoint(
                $summaries[$key]->value,
                $dataPointAttributes,
                $startTimestamp,
                $timestamp,
                $exemplars[$key] ?? [],
            );
        }

        return new Data\Gauge(
            $dataPoints,
        );
    }
}
