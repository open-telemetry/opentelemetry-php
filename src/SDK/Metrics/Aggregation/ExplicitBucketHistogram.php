<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Aggregation;

use function array_fill;
use function count;
use const INF;
use const NAN;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Aggregation;
use OpenTelemetry\SDK\Metrics\Data;

/**
 * @implements Aggregation<ExplicitBucketHistogramSummary>
 */
final class ExplicitBucketHistogram implements Aggregation
{
    /**
     * @var list<float|int>
     * @readonly
     */
    public array $boundaries;

    /**
     * @param list<float|int> $boundaries strictly ascending histogram bucket boundaries
     */
    public function __construct(array $boundaries)
    {
        $this->boundaries = $boundaries;
    }

    public function initialize(): ExplicitBucketHistogramSummary
    {
        return new ExplicitBucketHistogramSummary(
            0,
            0,
            +INF,
            -INF,
            array_fill(0, count($this->boundaries) + 1, 0),
        );
    }

    /**
     * @param ExplicitBucketHistogramSummary $summary
     */
    public function record($summary, $value, AttributesInterface $attributes, Context $context, int $timestamp): void
    {
        for ($i = 0; $i < count($this->boundaries) && $this->boundaries[$i] < $value; $i++) {
        }
        $summary->count++;
        $summary->sum += $value;
        $summary->min = self::min($value, $summary->min);
        $summary->max = self::max($value, $summary->max);
        $summary->buckets[$i]++;
    }

    /**
     * @param ExplicitBucketHistogramSummary $left
     * @param ExplicitBucketHistogramSummary $right
     */
    public function merge($left, $right): ExplicitBucketHistogramSummary
    {
        $count = $left->count + $right->count;
        $sum = $left->sum + $right->sum;
        $min = self::min($left->min, $right->min);
        $max = self::max($left->max, $right->max);
        $buckets = [];
        for ($i = 0; $i < count($this->boundaries) + 1; $i++) {
            $buckets[$i] = $left->buckets[$i] + $right->buckets[$i];
        }

        return new ExplicitBucketHistogramSummary(
            $count,
            $sum,
            $min,
            $max,
            $buckets,
        );
    }

    /**
     * @param ExplicitBucketHistogramSummary $left
     * @param ExplicitBucketHistogramSummary $right
     */
    public function diff($left, $right): ExplicitBucketHistogramSummary
    {
        $count = -$left->count + $right->count;
        $sum = -$left->sum + $right->sum;
        $min = $left->min > $right->min ? $right->min : NAN;
        $max = $left->max < $right->max ? $right->max : NAN;
        $buckets = [];
        for ($i = 0; $i < count($this->boundaries) + 1; $i++) {
            $buckets[$i] = -$left->buckets[$i] + $right->buckets[$i];
        }

        return new ExplicitBucketHistogramSummary(
            $count,
            $sum,
            $min,
            $max,
            $buckets,
        );
    }

    /**
     * @param array<ExplicitBucketHistogramSummary> $summaries
     */
    public function toData(
        array $attributes,
        array $summaries,
        array $exemplars,
        int $startTimestamp,
        int $timestamp,
        $temporality
    ): Data\Histogram {
        $dataPoints = [];
        foreach ($attributes as $key => $dataPointAttributes) {
            if (!$summaries[$key]->count) {
                continue;
            }

            $dataPoints[] = new Data\HistogramDataPoint(
                $summaries[$key]->count,
                $summaries[$key]->sum,
                $summaries[$key]->min,
                $summaries[$key]->max,
                $summaries[$key]->buckets,
                $this->boundaries,
                $dataPointAttributes,
                $startTimestamp,
                $timestamp,
                $exemplars[$key] ?? [],
            );
        }

        return new Data\Histogram(
            $dataPoints,
            $temporality,
        );
    }

    private static function min($left, $right)
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        return $left <= $right ? $left : ($right <= $left ? $right : NAN);
    }

    private static function max($left, $right)
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        return $left >= $right ? $left : ($right >= $left ? $right : NAN);
    }
}
