<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Aggregation;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\Metrics\Aggregation;
use OpenTelemetry\SDK\Metrics\Data;
use OpenTelemetry\SDK\Metrics\Data\Temporality;

/**
 * @implements Aggregation<LastValueSummary>
 */
final class LastValue implements Aggregation
{
    public function initialize(): LastValueSummary
    {
        return new LastValueSummary(null, 0);
    }

    /**
     * @param LastValueSummary $summary
     */
    public function record(mixed $summary, float|int $value, Attributes $attributes, Context $context, int $timestamp): void
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
    public function merge(mixed $left, mixed $right): LastValueSummary
    {
        return $right->timestamp >= $left->timestamp ? $right : $left;
    }

    /**
     * @param LastValueSummary $left
     * @param LastValueSummary $right
     */
    public function diff(mixed $left, mixed $right): LastValueSummary
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
        ?int $startTimestamp,
        int $timestamp,
        Temporality $temporality,
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
