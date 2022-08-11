<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Aggregation;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Aggregation\LastValueAggregation;
use OpenTelemetry\SDK\Metrics\Aggregation\LastValueSummary;
use OpenTelemetry\SDK\Metrics\Data\Gauge;
use OpenTelemetry\SDK\Metrics\Data\NumberDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\Exemplar\FixedSizeReservoir;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Metrics\Aggregation\LastValueAggregation
 * @covers \OpenTelemetry\SDK\Metrics\Aggregation\LastValueSummary
 */
final class LastValueAggregationTest extends TestCase
{
    public function test_initialize(): void
    {
        $this->assertEquals(
            new LastValueSummary(null, 0),
            (new LastValueAggregation())->initialize(),
        );
    }

    public function test_record(): void
    {
        $summary = new LastValueSummary(3, 0);
        (new LastValueAggregation())->record($summary, 5, Attributes::create([]), Context::getRoot(), 1);
        $this->assertEquals(
            new LastValueSummary(5, 1),
            $summary,
        );
    }

    public function test_record_older_timestamp(): void
    {
        $summary = new LastValueSummary(3, 2);
        (new LastValueAggregation())->record($summary, 5, Attributes::create([]), Context::getRoot(), 1);
        $this->assertEquals(
            new LastValueSummary(3, 2),
            $summary,
        );
    }

    public function test_merge(): void
    {
        $this->assertEquals(
            new LastValueSummary(5, 1),
            (new LastValueAggregation())->merge(new LastValueSummary(8, 0), new LastValueSummary(5, 1)),
        );
    }

    public function test_merge_older_timestamp(): void
    {
        $this->assertEquals(
            new LastValueSummary(8, 2),
            (new LastValueAggregation())->merge(new LastValueSummary(8, 2), new LastValueSummary(5, 1)),
        );
    }

    public function test_diff(): void
    {
        $this->assertEquals(
            new LastValueSummary(5, 1),
            (new LastValueAggregation())->diff(new LastValueSummary(8, 0), new LastValueSummary(5, 1)),
        );
    }

    public function test_diff_older_timestamp(): void
    {
        $this->assertEquals(
            new LastValueSummary(8, 2),
            (new LastValueAggregation())->diff(new LastValueSummary(8, 2), new LastValueSummary(5, 1)),
        );
    }

    public function test_to_data(): void
    {
        $this->assertEquals(
            new Gauge(
                [
                    new NumberDataPoint(
                        5,
                        Attributes::create([]),
                        0,
                        1,
                    ),
                ],
            ),
            (new LastValueAggregation())->toData(
                [Attributes::create([])],
                [new LastValueSummary(5, 1)],
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
            new Gauge(
                [
                ],
            ),
            (new LastValueAggregation())->toData(
                [Attributes::create([])],
                [new LastValueSummary(null, 0)],
                [],
                0,
                1,
                Temporality::DELTA,
            ),
        );
    }

    public function test_exemplar_reservoir(): void
    {
        $this->assertEquals(
            new FixedSizeReservoir(Attributes::factory()),
            (new LastValueAggregation())->exemplarReservoir(Attributes::factory()),
        );
    }
}
