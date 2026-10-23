<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Aggregation;

use const INF;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Aggregation\ExplicitBucketHistogramAggregation;
use OpenTelemetry\SDK\Metrics\Aggregation\ExplicitBucketHistogramSummary;
use OpenTelemetry\SDK\Metrics\Data\Histogram;
use OpenTelemetry\SDK\Metrics\Data\HistogramDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExplicitBucketHistogramAggregation::class)]
#[CoversClass(ExplicitBucketHistogramSummary::class)]
final class ExplicitBucketHistogramAggregationTest extends TestCase
{
    public function test_initialize(): void
    {
        $this->assertEquals(
            new ExplicitBucketHistogramSummary(0, 0, INF, -INF, [0, 0, 0]),
            (new ExplicitBucketHistogramAggregation([0, 5]))->initialize()
        );
    }

    public function test_record(): void
    {
        $summary = new ExplicitBucketHistogramSummary(2, 9, 3, 6, [0, 1, 1]);
        (new ExplicitBucketHistogramAggregation([0, 5]))->record($summary, 5, Attributes::create([]), Context::getRoot(), 1);
        $this->assertEquals(
            new ExplicitBucketHistogramSummary(3, 14, 3, 6, [0, 2, 1]),
            $summary,
        );
    }

    public function test_merge(): void
    {
        $this->assertEquals(
            new ExplicitBucketHistogramSummary(4, 17, 3, 6, [0, 3, 1]),
            (new ExplicitBucketHistogramAggregation([0, 5]))->merge(
                new ExplicitBucketHistogramSummary(1, 4, 4, 4, [0, 1, 0]),
                new ExplicitBucketHistogramSummary(3, 13, 3, 6, [0, 2, 1]),
            ),
        );
    }

    public function test_diff(): void
    {
        $this->assertEquals(
            new ExplicitBucketHistogramSummary(2, 9, 3, 6, [0, 1, 1]),
            (new ExplicitBucketHistogramAggregation([0, 5]))->diff(
                new ExplicitBucketHistogramSummary(1, 4, 4, 4, [0, 1, 0]),
                new ExplicitBucketHistogramSummary(3, 13, 3, 6, [0, 2, 1]),
            ),
        );
    }

    public function test_diff_with_current_min_drops_min(): void
    {
        $this->assertNan(
            (new ExplicitBucketHistogramAggregation([0, 5]))->diff(
                new ExplicitBucketHistogramSummary(1, 3, 3, 3, [0, 1, 0]),
                new ExplicitBucketHistogramSummary(3, 13, 3, 6, [0, 2, 1]),
            )->min,
        );
    }

    public function test_diff_with_current_max_drops_max(): void
    {
        $this->assertNan(
            (new ExplicitBucketHistogramAggregation([0, 5]))->diff(
                new ExplicitBucketHistogramSummary(1, 6, 6, 6, [0, 0, 1]),
                new ExplicitBucketHistogramSummary(3, 13, 3, 6, [0, 2, 1]),
            )->max,
        );
    }

    public function test_to_data(): void
    {
        $this->assertEquals(
            new Histogram(
                [
                    new HistogramDataPoint(
                        3,
                        13,
                        3,
                        6,
                        [0, 2, 1],
                        [0, 5],
                        Attributes::create([]),
                        0,
                        1,
                    ),
                ],
                Temporality::DELTA,
            ),
            (new ExplicitBucketHistogramAggregation([0, 5]))->toData(
                [Attributes::create([])],
                [new ExplicitBucketHistogramSummary(3, 13, 3, 6, [0, 2, 1])],
                [],
                0,
                1,
                Temporality::DELTA,
            ),
        );
    }

    public function test_to_data_empty(): void
    {
        $this->assertEquals(
            new Histogram(
                [
                ],
                Temporality::DELTA,
            ),
            (new ExplicitBucketHistogramAggregation([0, 5]))->toData(
                [Attributes::create([])],
                [new ExplicitBucketHistogramSummary(0, 0, INF, -INF, [0, 0, 0])],
                [],
                0,
                1,
                Temporality::DELTA,
            ),
        );
    }

    public function test_without_min_max_records_neither(): void
    {
        $aggregation = new ExplicitBucketHistogramAggregation([0, 5], recordMinMax: false);
        $summary = $aggregation->initialize();
        $aggregation->record($summary, 3, Attributes::create([]), Context::getRoot(), 1);
        $aggregation->record($summary, 6, Attributes::create([]), Context::getRoot(), 1);

        $dataPoints = [];
        foreach ($aggregation->toData([Attributes::create([])], [$summary], [], 0, 1, Temporality::DELTA)->dataPoints as $dataPoint) {
            $dataPoints[] = $dataPoint;
        }

        $this->assertCount(1, $dataPoints);
        $this->assertSame(2, $dataPoints[0]->count);
        $this->assertSame(9, $dataPoints[0]->sum);
        $this->assertSame([0, 1, 1], $dataPoints[0]->bucketCounts);
        $this->assertNan($dataPoints[0]->min);
        $this->assertNan($dataPoints[0]->max);
    }
}
