<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Data\HistogramDataPoint;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HistogramDataPoint::class)]
final class HistogramDataPointTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $attributes = $this->createMock(AttributesInterface::class);

        $dataPoint = new HistogramDataPoint(
            count: 10,
            sum: 55.5,
            min: 1.0,
            max: 10.0,
            bucketCounts: [2, 3, 5],
            explicitBounds: [5.0, 10.0],
            attributes: $attributes,
            startTimestamp: 1000,
            timestamp: 2000,
            exemplars: [],
        );

        $this->assertSame(10, $dataPoint->count);
        $this->assertSame(55.5, $dataPoint->sum);
        $this->assertSame(1.0, $dataPoint->min);
        $this->assertSame(10.0, $dataPoint->max);
        $this->assertSame([2, 3, 5], $dataPoint->bucketCounts);
        $this->assertSame([5.0, 10.0], $dataPoint->explicitBounds);
        $this->assertSame($attributes, $dataPoint->attributes);
        $this->assertSame(1000, $dataPoint->startTimestamp);
        $this->assertSame(2000, $dataPoint->timestamp);
        $this->assertSame([], $dataPoint->exemplars);
    }

    public function test_constructor_with_integer_values(): void
    {
        $attributes = $this->createMock(AttributesInterface::class);

        $dataPoint = new HistogramDataPoint(
            count: 5,
            sum: 100,
            min: 10,
            max: 30,
            bucketCounts: [1, 2, 2],
            explicitBounds: [15, 25],
            attributes: $attributes,
            startTimestamp: 500,
            timestamp: 1000,
        );

        $this->assertSame(100, $dataPoint->sum);
        $this->assertSame(10, $dataPoint->min);
        $this->assertSame(30, $dataPoint->max);
    }

    public function test_exemplars_defaults_to_empty(): void
    {
        $attributes = $this->createMock(AttributesInterface::class);

        $dataPoint = new HistogramDataPoint(
            count: 0,
            sum: 0,
            min: 0,
            max: 0,
            bucketCounts: [],
            explicitBounds: [],
            attributes: $attributes,
            startTimestamp: 0,
            timestamp: 0,
        );

        $this->assertSame([], $dataPoint->exemplars);
    }
}
