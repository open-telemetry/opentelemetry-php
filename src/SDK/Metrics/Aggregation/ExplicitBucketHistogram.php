<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Aggregation;

use OpenTelemetry\Context;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\Metrics\Aggregation;
use OpenTelemetry\SDK\Metrics\Data;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use function array_fill;
use function count;

/**
 * @implements Aggregation<ExplicitBucketHistogramSummary>
 */
final class ExplicitBucketHistogram implements Aggregation {

    public readonly array $boundaries;

    public function __construct(array $boundaries) {
        $this->boundaries = $boundaries;
    }

    public function initialize(): ExplicitBucketHistogramSummary {
        return new ExplicitBucketHistogramSummary(
            0,
            0,
            array_fill(0, count($this->boundaries) + 1, 0),
        );
    }

    /**
     * @param ExplicitBucketHistogramSummary $summary
     */
    public function record(mixed $summary, float|int $value, Attributes $attributes, Context $context, int $timestamp): void {
        for ($i = 0; $i < count($this->boundaries) && $this->boundaries[$i] < $value; $i++) {}
        $summary->count++;
        $summary->sum += $value;
        $summary->buckets[$i]++;
    }

    /**
     * @param ExplicitBucketHistogramSummary $left
     * @param ExplicitBucketHistogramSummary $right
     */
    public function merge(mixed $left, mixed $right): ExplicitBucketHistogramSummary {
        $count = $left->count + $right->count;
        $sum = $left->sum + $right->sum;
        $buckets = [];
        for ($i = 0; $i < count($this->boundaries) + 1; $i++) {
            $buckets[$i] = $left->buckets[$i] + $right->buckets[$i];
        }

        return new ExplicitBucketHistogramSummary(
            $count,
            $sum,
            $buckets,
        );
    }

    /**
     * @param ExplicitBucketHistogramSummary $left
     * @param ExplicitBucketHistogramSummary $right
     */
    public function diff(mixed $left, mixed $right): ExplicitBucketHistogramSummary {
        $count = -$left->count + $right->count;
        $sum = -$left->sum + $right->sum;
        $buckets = [];
        for ($i = 0; $i < count($this->boundaries) + 1; $i++) {
            $buckets[$i] = -$left->buckets[$i] + $right->buckets[$i];
        }

        return new ExplicitBucketHistogramSummary(
            $count,
            $sum,
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
        ?int $startTimestamp,
        int $timestamp,
        Temporality $temporality,
    ): Data\Histogram {
        $dataPoints = [];
        foreach ($attributes as $key => $dataPointAttributes) {
            $dataPoints[] = new Data\HistogramDataPoint(
                $summaries[$key]->count,
                $summaries[$key]->sum,
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
}
