<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Aggregation;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Aggregation\SumAggregation;
use OpenTelemetry\SDK\Metrics\Aggregation\SumSummary;
use OpenTelemetry\SDK\Metrics\Data\NumberDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Sum;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\Exemplar\FixedSizeReservoir;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Metrics\Aggregation\SumAggregation
 * @covers \OpenTelemetry\SDK\Metrics\Aggregation\SumSummary
 */
final class SumAggregationTest extends TestCase
{
    public function test_initialize(): void
    {
        $this->assertEquals(
            new SumSummary(0),
            (new SumAggregation())->initialize(),
        );
    }

    public function test_record(): void
    {
        $summary = new SumSummary(3);
        (new SumAggregation())->record($summary, 5, Attributes::create([]), Context::getRoot(), 1);
        $this->assertEquals(
            new SumSummary(8),
            $summary,
        );
    }

    public function test_merge(): void
    {
        $this->assertEquals(
            new SumSummary(13),
            (new SumAggregation())->merge(
                new SumSummary(8),
                new SumSummary(5),
            ),
        );
    }

    public function test_diff(): void
    {
        $this->assertEquals(
            new SumSummary(-3),
            (new SumAggregation())->diff(
                new SumSummary(8),
                new SumSummary(5),
            ),
        );
    }

    public function test_to_data(): void
    {
        $this->assertEquals(
            new Sum(
                [
                    new NumberDataPoint(
                        5,
                        Attributes::create([]),
                        0,
                        1,
                    ),
                ],
                Temporality::DELTA,
                false,
            ),
            (new SumAggregation())->toData(
                [Attributes::create([])],
                [new SumSummary(5)],
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
            (new SumAggregation())->exemplarReservoir(Attributes::factory()),
        );
    }
}
