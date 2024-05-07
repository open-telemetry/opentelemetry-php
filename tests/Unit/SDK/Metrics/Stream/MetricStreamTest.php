<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Stream;

use OpenTelemetry\API\Behavior\Internal\Logging;
use function current;
use function extension_loaded;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Aggregation\SumAggregation;
use OpenTelemetry\SDK\Metrics\Aggregation\SumSummary;
use OpenTelemetry\SDK\Metrics\AttributeProcessor\FilteredAttributeProcessor;
use OpenTelemetry\SDK\Metrics\Data;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoirInterface;
use OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\Stream\Metric;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregator;
use OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricStream;
use const PHP_INT_SIZE;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricStream
 * @covers \OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricStream
 *
 * @covers \OpenTelemetry\SDK\Metrics\Stream\Metric
 * @covers \OpenTelemetry\SDK\Metrics\Stream\MetricAggregator
 *
 * @uses \OpenTelemetry\SDK\Metrics\Stream\Delta
 * @uses \OpenTelemetry\SDK\Metrics\Stream\DeltaStorage
 *
 * @uses \OpenTelemetry\SDK\Metrics\Data\NumberDataPoint
 * @uses \OpenTelemetry\SDK\Metrics\Data\Sum
 * @uses \OpenTelemetry\SDK\Metrics\Data\Temporality
 *
 * @uses \OpenTelemetry\SDK\Metrics\Aggregation\SumAggregation
 * @uses \OpenTelemetry\SDK\Metrics\Aggregation\SumSummary
 *
 * @uses \OpenTelemetry\SDK\Common\Attribute\Attributes
 * @uses \OpenTelemetry\SDK\Common\Attribute\AttributesBuilder
 * @uses \OpenTelemetry\SDK\Common\Attribute\AttributesFactory
 */
final class MetricStreamTest extends TestCase
{
    public function setUp(): void
    {
        Logging::disable();
    }

    public function test_asynchronous_single_data_point(): void
    {
        $s = new AsynchronousMetricStream(new SumAggregation(), 3);

        $d = $s->register(Temporality::DELTA);
        $c = $s->register(Temporality::CUMULATIVE);

        $s->push(new Metric([Attributes::create([])], [new SumSummary(5)], 5));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create([]), 3, 5),
        ], Temporality::DELTA, false), $s->collect($d));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create([]), 3, 5),
        ], Temporality::CUMULATIVE, false), $s->collect($c));

        $s->push(new Metric([Attributes::create([])], [new SumSummary(7)], 8));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(2, Attributes::create([]), 5, 8),
        ], Temporality::DELTA, false), $s->collect($d));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(7, Attributes::create([]), 3, 8),
        ], Temporality::CUMULATIVE, false), $s->collect($c));

        $s->push(new Metric([Attributes::create([])], [new SumSummary(3)], 12));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(-4, Attributes::create([]), 8, 12),
        ], Temporality::DELTA, false), $s->collect($d));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(3, Attributes::create([]), 3, 12),
        ], Temporality::CUMULATIVE, false), $s->collect($c));
    }

    public function test_asynchronous_multiple_data_points(): void
    {
        $s = new AsynchronousMetricStream(new SumAggregation(), 3);

        $d = $s->register(Temporality::DELTA);
        $c = $s->register(Temporality::CUMULATIVE);

        $s->push(new Metric([Attributes::create(['status' => 300]), Attributes::create(['status' => 400])], [new SumSummary(5), new SumSummary(2)], 5));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create(['status' => 300]), 3, 5),
            new Data\NumberDataPoint(2, Attributes::create(['status' => 400]), 3, 5),
        ], Temporality::DELTA, false), $s->collect($d));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create(['status' => 300]), 3, 5),
            new Data\NumberDataPoint(2, Attributes::create(['status' => 400]), 3, 5),
        ], Temporality::CUMULATIVE, false), $s->collect($c));

        $s->push(new Metric([Attributes::create(['status' => 300]), Attributes::create(['status' => 400])], [new SumSummary(2), new SumSummary(7)], 8));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(-3, Attributes::create(['status' => 300]), 5, 8),
            new Data\NumberDataPoint(5, Attributes::create(['status' => 400]), 5, 8),
        ], Temporality::DELTA, false), $s->collect($d));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(2, Attributes::create(['status' => 300]), 3, 8),
            new Data\NumberDataPoint(7, Attributes::create(['status' => 400]), 3, 8),
        ], Temporality::CUMULATIVE, false), $s->collect($c));
    }

    public function test_asynchronous_omit_data_point(): void
    {
        $s = new AsynchronousMetricStream(new SumAggregation(), 3);

        $d = $s->register(Temporality::DELTA);
        $c = $s->register(Temporality::CUMULATIVE);

        $s->push(new Metric([Attributes::create([])], [new SumSummary(5)], 5));
        $s->collect($d);
        $s->collect($c);

        $s->push(new Metric([], [], 7));
        $this->assertEquals(new Data\Sum([
        ], Temporality::DELTA, false), $s->collect($d));
        $this->assertEquals(new Data\Sum([
        ], Temporality::CUMULATIVE, false), $s->collect($c));

        $s->push(new Metric([Attributes::create([])], [new SumSummary(3)], 12));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(3, Attributes::create([]), 7, 12),
        ], Temporality::DELTA, false), $s->collect($d));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(3, Attributes::create([]), 3, 12),
        ], Temporality::CUMULATIVE, false), $s->collect($c));
    }

    public function test_synchronous_single_data_point(): void
    {
        $s = new SynchronousMetricStream(new SumAggregation(), 3);

        $d = $s->register(Temporality::DELTA);
        $c = $s->register(Temporality::CUMULATIVE);

        $s->push(new Metric([Attributes::create([])], [new SumSummary(5)], 5));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create([]), 3, 5),
        ], Temporality::DELTA, false), $s->collect($d));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create([]), 3, 5),
        ], Temporality::CUMULATIVE, false), $s->collect($c));

        $s->push(new Metric([Attributes::create([])], [new SumSummary(2)], 8));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(2, Attributes::create([]), 5, 8),
        ], Temporality::DELTA, false), $s->collect($d));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(7, Attributes::create([]), 3, 8),
        ], Temporality::CUMULATIVE, false), $s->collect($c));

        $s->push(new Metric([Attributes::create([])], [new SumSummary(-4)], 12));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(-4, Attributes::create([]), 8, 12),
        ], Temporality::DELTA, false), $s->collect($d));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(3, Attributes::create([]), 3, 12),
        ], Temporality::CUMULATIVE, false), $s->collect($c));
    }

    public function test_synchronous_multiple_data_points(): void
    {
        $s = new SynchronousMetricStream(new SumAggregation(), 3);

        $d = $s->register(Temporality::DELTA);
        $c = $s->register(Temporality::CUMULATIVE);

        $s->push(new Metric([Attributes::create(['status' => 300]), Attributes::create(['status' => 400])], [new SumSummary(5), new SumSummary(2)], 5));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create(['status' => 300]), 3, 5),
            new Data\NumberDataPoint(2, Attributes::create(['status' => 400]), 3, 5),
        ], Temporality::DELTA, false), $s->collect($d));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create(['status' => 300]), 3, 5),
            new Data\NumberDataPoint(2, Attributes::create(['status' => 400]), 3, 5),
        ], Temporality::CUMULATIVE, false), $s->collect($c));

        $s->push(new Metric([Attributes::create(['status' => 300]), Attributes::create(['status' => 400])], [new SumSummary(-3), new SumSummary(5)], 8));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(-3, Attributes::create(['status' => 300]), 5, 8),
            new Data\NumberDataPoint(5, Attributes::create(['status' => 400]), 5, 8),
        ], Temporality::DELTA, false), $s->collect($d));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(2, Attributes::create(['status' => 300]), 3, 8),
            new Data\NumberDataPoint(7, Attributes::create(['status' => 400]), 3, 8),
        ], Temporality::CUMULATIVE, false), $s->collect($c));
    }

    public function test_asynchronous_temporality(): void
    {
        $s = new AsynchronousMetricStream(new SumAggregation(), 3);
        $this->assertSame(Temporality::CUMULATIVE, $s->temporality());
    }

    public function test_synchronous_temporality(): void
    {
        $s = new SynchronousMetricStream(new SumAggregation(), 3);
        $this->assertSame(Temporality::DELTA, $s->temporality());
    }

    public function test_asynchronous_timestamp_returns_last_metric_timestamp(): void
    {
        $s = new AsynchronousMetricStream(new SumAggregation(), 3);
        $this->assertSame(3, $s->timestamp());

        $s->push(new Metric([], [], 5));
        $this->assertSame(5, $s->timestamp());
    }

    public function test_synchronous_timestamp_returns_last_metric_timestamp(): void
    {
        $s = new SynchronousMetricStream(new SumAggregation(), 3);
        $this->assertSame(3, $s->timestamp());

        $s->push(new Metric([], [], 5));
        $this->assertSame(5, $s->timestamp());
    }

    public function test_asynchronous_unregister_removes_reader(): void
    {
        /** @var int|null $value */
        $value = null;

        $s = new AsynchronousMetricStream(new SumAggregation(), 3);

        $s->push(new Metric([Attributes::create([])], [new SumSummary(5)], 7));
        $d = $s->register(Temporality::DELTA);
        $s->collect($d);
        $s->unregister($d);

        $s->push(new Metric([Attributes::create([])], [new SumSummary(5)], 7));
        // Implementation treats unknown reader as cumulative reader
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create([]), 3, 7),
        ], Temporality::CUMULATIVE, false), $s->collect($d));
    }

    public function test_synchronous_unregister_removes_reader(): void
    {
        $s = new SynchronousMetricStream(new SumAggregation(), 3);

        $c = $s->register(Temporality::CUMULATIVE);
        $s->push(new Metric([Attributes::create([])], [new SumSummary(5)], 5));
        $s->collect($c);
        $s->unregister($c);

        $s->push(new Metric([Attributes::create([])], [new SumSummary(-5)], 7));
        $this->assertEquals(new Data\Sum([
        ], Temporality::DELTA, false), $s->collect($c));
    }

    public function test_asynchronous_unregister_invalid_does_not_affect_reader(): void
    {
        $s = new AsynchronousMetricStream(new SumAggregation(), 3);

        $d = $s->register(Temporality::DELTA);
        $s->push(new Metric([Attributes::create([])], [new SumSummary(5)], 5));
        $s->collect($d);
        $s->unregister($d + 1);

        $s->push(new Metric([Attributes::create([])], [new SumSummary(5)], 7));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(0, Attributes::create([]), 5, 7),
        ], Temporality::DELTA, false), $s->collect($d));
    }

    public function test_synchronous_unregister_invalid_does_not_affect_reader(): void
    {
        $s = new SynchronousMetricStream(new SumAggregation(), 3);

        $c = $s->register(Temporality::CUMULATIVE);
        $s->push(new Metric([Attributes::create([])], [new SumSummary(5)], 5));
        $s->collect($c);
        $s->unregister($c + 1);

        $s->push(new Metric([Attributes::create([])], [new SumSummary(-5)], 7));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(0, Attributes::create([]), 3, 7),
        ], Temporality::CUMULATIVE, false), $s->collect($c));
    }

    public function test_synchronous_reader_limit_exceeded_triggers_warning(): void
    {
        if (extension_loaded('gmp')) {
            $this->markTestSkipped();
        }

        $s = new SynchronousMetricStream(new SumAggregation(), 3);

        for ($i = 0; $i < (PHP_INT_SIZE << 3) - 1; $i++) {
            $s->register(Temporality::DELTA);
        }

        $r = $s->register(Temporality::DELTA);
        $this->assertSame(PHP_INT_SIZE << 3, $r);
    }

    public function test_synchronous_reader_limit_exceeded_returns_noop_reader(): void
    {
        if (extension_loaded('gmp')) {
            $this->markTestSkipped();
        }

        $s = new SynchronousMetricStream(new SumAggregation(), 3);

        for ($i = 0; $i < (PHP_INT_SIZE << 3) - 1; $i++) {
            $s->register(Temporality::DELTA);
        }

        $d = @$s->register(Temporality::DELTA);

        $s->push(new Metric([Attributes::create([])], [new SumSummary(5)], 5));
        $this->assertEquals(new Data\Sum([
        ], Temporality::DELTA, false), $s->collect($d));
    }

    public function test_synchronous_reader_limit_does_not_apply_if_gmp_available(): void
    {
        if (!extension_loaded('gmp')) {
            $this->markTestSkipped();
        }

        $s = new SynchronousMetricStream(new SumAggregation(), 3);

        for ($i = 0; $i < (PHP_INT_SIZE << 3) - 1; $i++) {
            $s->register(Temporality::DELTA);
        }

        $d = $s->register(Temporality::DELTA);

        $s->push(new Metric([Attributes::create([])], [new SumSummary(5)], 5));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create([]), 3, 5),
        ], Temporality::DELTA, false), $s->collect($d));
    }

    public function test_metric_aggregator_applies_attribute_filter(): void
    {
        $aggregator = new MetricAggregator(new FilteredAttributeProcessor(['foo', 'bar']), new SumAggregation(), null);
        $aggregator->record(5, Attributes::create(['foo' => 1, 'bar' => 2, 'baz' => 3]), Context::getRoot(), 0);

        $this->assertEquals(
            Attributes::create(['foo' => 1, 'bar' => 2]),
            current($aggregator->collect(1)->attributes),
        );
    }

    public function test_metric_aggregator_forwards_to_exemplar_filter(): void
    {
        $exemplarReservoir = $this->createMock(ExemplarReservoirInterface::class);
        $exemplarReservoir->expects($this->once())->method('offer')->with($this->anything(), 5, Attributes::create(['foo' => 1]), Context::getRoot(), 3);
        $aggregator = new MetricAggregator(new FilteredAttributeProcessor(['foo', 'bar']), new SumAggregation(), $exemplarReservoir);
        $aggregator->record(5, Attributes::create(['foo' => 1]), Context::getRoot(), 3);
    }
}
