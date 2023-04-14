<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Stream;

use function memory_get_usage;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Aggregation\SumAggregation;
use OpenTelemetry\SDK\Metrics\Aggregation\SumSummary;
use OpenTelemetry\SDK\Metrics\Stream\DeltaStorage;
use OpenTelemetry\SDK\Metrics\Stream\Metric;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Metrics\Stream\DeltaStorage
 * @covers \OpenTelemetry\SDK\Metrics\Stream\Delta
 * @covers \OpenTelemetry\SDK\Metrics\Stream\Metric
 *
 * @uses \OpenTelemetry\SDK\Metrics\Aggregation\SumAggregation
 * @uses \OpenTelemetry\SDK\Metrics\Aggregation\SumSummary
 */
final class DeltaStorageTest extends TestCase
{
    public function test_empty_storage_returns_empty_metrics(): void
    {
        $ds = new DeltaStorage(new SumAggregation());

        $metric = $ds->collect(0b1);
        $this->assertNull($metric);
    }

    public function test_storage_returns_inserted_metric(): void
    {
        $ds = new DeltaStorage(new SumAggregation());

        $ds->add(new Metric([Attributes::create(['a'])], [new SumSummary(3)], 0), 0b1);

        $metric = $ds->collect(0);
        $this->assertEquals(new Metric(
            [Attributes::create(['a'])],
            [new SumSummary(3)],
            0,
        ), $metric);
    }

    public function test_storage_returns_inserted_metric_cumulative(): void
    {
        $ds = new DeltaStorage(new SumAggregation());

        $ds->add(new Metric([Attributes::create(['a'])], [new SumSummary(3)], 0), 0b1);
        $ds->collect(0, true);
        $ds->add(new Metric([Attributes::create(['a'])], [new SumSummary(5)], 1), 0b1);
        for ($i = 0; $i < 2; $i++) {
            $metric = $ds->collect(0, true);
            $this->assertEquals(new Metric(
                [Attributes::create(['a'])],
                [new SumSummary(8)],
                0,
            ), $metric);
        }
    }

    public function test_storage_does_not_return_zero_reader_metric(): void
    {
        $ds = new DeltaStorage(new SumAggregation());

        $ds->add(new Metric([Attributes::create(['a'])], [new SumSummary(3)], 0), 0b0);

        $this->assertNull($ds->collect(0));
    }

    public function test_storage_keeps_metrics_for_additional_readers(): void
    {
        $ds = new DeltaStorage(new SumAggregation());

        $ds->add(new Metric([Attributes::create(['a'])], [new SumSummary(3)], 0), 0b111);
        $metric = $ds->collect(0);
        $this->assertEquals(new Metric(
            [Attributes::create(['a'])],
            [new SumSummary(3)],
            0,
        ), $metric);

        $ds->add(new Metric([Attributes::create(['a']), Attributes::create(['b'])], [new SumSummary(7), new SumSummary(12)], 1), 0b111);
        $metric = $ds->collect(1);
        $this->assertEquals(new Metric(
            [Attributes::create(['a']), Attributes::create(['b'])],
            [new SumSummary(10), new SumSummary(12)],
            0,
        ), $metric);

        $ds->add(new Metric([Attributes::create(['a']), Attributes::create(['b'])], [new SumSummary(5), new SumSummary(9)], 2), 0b111);
        $metric = $ds->collect(1);
        $this->assertEquals(new Metric(
            [Attributes::create(['a']), Attributes::create(['b'])],
            [new SumSummary(5), new SumSummary(9)],
            2,
        ), $metric);

        $metric = $ds->collect(0);
        $this->assertEquals(new Metric(
            [Attributes::create(['a']), Attributes::create(['b'])],
            [new SumSummary(12), new SumSummary(21)],
            1,
        ), $metric);

        $metric = $ds->collect(2);
        $this->assertEquals(new Metric(
            [Attributes::create(['a']), Attributes::create(['b'])],
            [new SumSummary(15), new SumSummary(21)],
            0,
        ), $metric);
    }

    public function test_storage_keeps_constant_memory_on_one_active_reader(): void
    {
        $ds = new DeltaStorage(new SumAggregation());
        $ds->add(new Metric([Attributes::create(['a'])], [new SumSummary(0)], 0), 0b11);
        $ds->add(new Metric([Attributes::create(['a'])], [new SumSummary(0)], 0), 0b11);

        $memory = memory_get_usage();
        for ($i = 0; $i < 10000; $i++) {
            $ds->add(new Metric([Attributes::create(['a'])], [new SumSummary(1)], 0), 0b11);
            $ds->collect(0);
        }
        $this->assertLessThan(2000, memory_get_usage() - $memory);

        $metric = $ds->collect(1);
        $this->assertSame(10000, $metric->summaries[0]->value ?? null);
    }

    public function test_storage_keeps_constant_memory_on_one_active_retaining_reader(): void
    {
        $ds = new DeltaStorage(new SumAggregation());
        $ds->add(new Metric([Attributes::create(['a'])], [new SumSummary(0)], 0), 0b01);
        $ds->add(new Metric([Attributes::create(['a'])], [new SumSummary(0)], 0), 0b11);

        $memory = memory_get_usage();
        for ($i = 0; $i < 10000; $i++) {
            $ds->add(new Metric([Attributes::create(['a'])], [new SumSummary(1)], 0), 0b11);
            $ds->collect(0, true);
        }
        $this->assertLessThan(2000, memory_get_usage() - $memory);

        $metric = $ds->collect(1);
        $this->assertSame(10000, $metric->summaries[0]->value ?? null);
        $metric = $ds->collect(0);
        $this->assertSame(10000, $metric->summaries[0]->value ?? null);
    }

    public function test_storage_keeps_constant_memory_on_alternating_active_retaining_readers(): void
    {
        $ds = new DeltaStorage(new SumAggregation());
        $ds->add(new Metric([Attributes::create(['a'])], [new SumSummary(0)], 0), 0b100);
        $ds->add(new Metric([Attributes::create(['a'])], [new SumSummary(0)], 0), 0b110);
        $ds->add(new Metric([Attributes::create(['a'])], [new SumSummary(0)], 0), 0b111);

        $memory = memory_get_usage();
        for ($i = 0; $i < 10000; $i++) {
            $ds->add(new Metric([Attributes::create(['a'])], [new SumSummary(1)], 0), 0b111);
            $ds->collect($i % 3, true);
        }
        $this->assertLessThan(2000, memory_get_usage() - $memory);

        $metric = $ds->collect(2);
        $this->assertSame(10000, $metric->summaries[0]->value ?? null);
        $metric = $ds->collect(1);
        $this->assertSame(10000, $metric->summaries[0]->value ?? null);
        $metric = $ds->collect(0);
        $this->assertSame(10000, $metric->summaries[0]->value ?? null);
    }
}
