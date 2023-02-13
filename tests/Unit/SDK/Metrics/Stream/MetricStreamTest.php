<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Stream;

use function current;
use function extension_loaded;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Aggregation\SumAggregation;
use OpenTelemetry\SDK\Metrics\AttributeProcessor\FilteredAttributeProcessor;
use OpenTelemetry\SDK\Metrics\Data;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoirInterface;
use OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregator;
use OpenTelemetry\SDK\Metrics\Stream\StreamWriter;
use OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricStream;
use const PHP_INT_SIZE;
use PHPUnit\Framework\Exception as PHPUnitFrameworkException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricStream
 * @covers \OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricStreamObserver
 *
 * @covers \OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricStream
 *
 * @covers \OpenTelemetry\SDK\Metrics\Stream\Metric
 * @covers \OpenTelemetry\SDK\Metrics\Stream\MetricAggregator
 *
 * @uses \OpenTelemetry\SDK\Metrics\Stream\Delta
 * @uses \OpenTelemetry\SDK\Metrics\Stream\DeltaStorage
 * @uses \OpenTelemetry\SDK\Metrics\Stream\StreamWriter
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
    public function test_asynchronous_single_data_point(): void
    {
        /** @var int|null $value */
        $value = null;

        $s = new AsynchronousMetricStream(Attributes::factory(), null, new SumAggregation(), null, function (ObserverInterface $observer) use (&$value): void {
            $observer->observe($value);
        }, 3);

        $d = $s->register(Temporality::DELTA);
        $c = $s->register(Temporality::CUMULATIVE);

        $value = 5;
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create([]), 3, 5),
        ], Temporality::DELTA, false), $s->collect($d, 5));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create([]), 3, 5),
        ], Temporality::CUMULATIVE, false), $s->collect($c, 5));

        $value = 7;
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(2, Attributes::create([]), 5, 8),
        ], Temporality::DELTA, false), $s->collect($d, 8));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(7, Attributes::create([]), 3, 8),
        ], Temporality::CUMULATIVE, false), $s->collect($c, 8));

        $value = 3;
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(-4, Attributes::create([]), 8, 12),
        ], Temporality::DELTA, false), $s->collect($d, 12));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(3, Attributes::create([]), 3, 12),
        ], Temporality::CUMULATIVE, false), $s->collect($c, 12));
    }

    public function test_asynchronous_multiple_data_points(): void
    {
        /** @var int|null $m */
        $m = null;

        $s = new AsynchronousMetricStream(Attributes::factory(), null, new SumAggregation(), null, function (ObserverInterface $observer) use (&$m): void {
            if ($m === 0) {
                $observer->observe(5, ['status' => 300]);
                $observer->observe(2, ['status' => 400]);
            }
            if ($m === 1) {
                $observer->observe(2, ['status' => 300]);
                $observer->observe(7, ['status' => 400]);
            }
        }, 3);

        $d = $s->register(Temporality::DELTA);
        $c = $s->register(Temporality::CUMULATIVE);

        $m = 0;
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create(['status' => 300]), 3, 5),
            new Data\NumberDataPoint(2, Attributes::create(['status' => 400]), 3, 5),
        ], Temporality::DELTA, false), $s->collect($d, 5));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create(['status' => 300]), 3, 5),
            new Data\NumberDataPoint(2, Attributes::create(['status' => 400]), 3, 5),
        ], Temporality::CUMULATIVE, false), $s->collect($c, 5));

        $m = 1;
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(-3, Attributes::create(['status' => 300]), 5, 8),
            new Data\NumberDataPoint(5, Attributes::create(['status' => 400]), 5, 8),
        ], Temporality::DELTA, false), $s->collect($d, 8));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(2, Attributes::create(['status' => 300]), 3, 8),
            new Data\NumberDataPoint(7, Attributes::create(['status' => 400]), 3, 8),
        ], Temporality::CUMULATIVE, false), $s->collect($c, 8));
    }

    public function test_asynchronous_omit_data_point(): void
    {
        /** @var int|null $value */
        $value = null;

        $s = new AsynchronousMetricStream(Attributes::factory(), null, new SumAggregation(), null, function (ObserverInterface $observer) use (&$value): void {
            if ($value !== null) {
                $observer->observe($value);
            }
        }, 3);

        $d = $s->register(Temporality::DELTA);
        $c = $s->register(Temporality::CUMULATIVE);

        $value = 5;
        $s->collect($d, 5);
        $s->collect($c, 5);

        $value = null;
        $this->assertEquals(new Data\Sum([
        ], Temporality::DELTA, false), $s->collect($d, 7));
        $this->assertEquals(new Data\Sum([
        ], Temporality::CUMULATIVE, false), $s->collect($c, 7));

        $value = 3;
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(3, Attributes::create([]), 7, 12),
        ], Temporality::DELTA, false), $s->collect($d, 12));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(3, Attributes::create([]), 3, 12),
        ], Temporality::CUMULATIVE, false), $s->collect($c, 12));
    }

    public function test_synchronous_single_data_point(): void
    {
        $s = new SynchronousMetricStream(null, new SumAggregation(), null, 3);
        $w = new StreamWriter(null, Attributes::factory(), $s->writable());

        $d = $s->register(Temporality::DELTA);
        $c = $s->register(Temporality::CUMULATIVE);

        $w->record(5, [], null, 4);
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create([]), 3, 5),
        ], Temporality::DELTA, false), $s->collect($d, 5));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create([]), 3, 5),
        ], Temporality::CUMULATIVE, false), $s->collect($c, 5));

        $w->record(2, [], null, 7);
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(2, Attributes::create([]), 5, 8),
        ], Temporality::DELTA, false), $s->collect($d, 8));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(7, Attributes::create([]), 3, 8),
        ], Temporality::CUMULATIVE, false), $s->collect($c, 8));

        $w->record(-4, [], null, 9);
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(-4, Attributes::create([]), 8, 12),
        ], Temporality::DELTA, false), $s->collect($d, 12));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(3, Attributes::create([]), 3, 12),
        ], Temporality::CUMULATIVE, false), $s->collect($c, 12));
    }

    public function test_synchronous_multiple_data_points(): void
    {
        $s = new SynchronousMetricStream(null, new SumAggregation(), null, 3);
        $w = new StreamWriter(null, Attributes::factory(), $s->writable());

        $d = $s->register(Temporality::DELTA);
        $c = $s->register(Temporality::CUMULATIVE);

        $w->record(5, ['status' => 300], null, 4);
        $w->record(2, ['status' => 400], null, 4);
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create(['status' => 300]), 3, 5),
            new Data\NumberDataPoint(2, Attributes::create(['status' => 400]), 3, 5),
        ], Temporality::DELTA, false), $s->collect($d, 5));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create(['status' => 300]), 3, 5),
            new Data\NumberDataPoint(2, Attributes::create(['status' => 400]), 3, 5),
        ], Temporality::CUMULATIVE, false), $s->collect($c, 5));

        $w->record(-3, ['status' => 300], null, 7);
        $w->record(5, ['status' => 400], null, 7);
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(-3, Attributes::create(['status' => 300]), 5, 8),
            new Data\NumberDataPoint(5, Attributes::create(['status' => 400]), 5, 8),
        ], Temporality::DELTA, false), $s->collect($d, 8));
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(2, Attributes::create(['status' => 300]), 3, 8),
            new Data\NumberDataPoint(7, Attributes::create(['status' => 400]), 3, 8),
        ], Temporality::CUMULATIVE, false), $s->collect($c, 8));
    }

    public function test_asynchronous_temporality(): void
    {
        $s = new AsynchronousMetricStream(Attributes::factory(), null, new SumAggregation(), null, fn (ObserverInterface $observer) => $observer->observe(1), 3);
        $this->assertSame(Temporality::CUMULATIVE, $s->temporality());
    }

    public function test_synchronous_temporality(): void
    {
        $s = new SynchronousMetricStream(null, new SumAggregation(), null, 3);
        $this->assertSame(Temporality::DELTA, $s->temporality());
    }

    public function test_asynchronous_collection_timestamp_returns_last_collection_timestamp(): void
    {
        $s = new AsynchronousMetricStream(Attributes::factory(), null, new SumAggregation(), null, fn (ObserverInterface $observer) => $observer->observe(1), 3);
        $this->assertSame(3, $s->collectionTimestamp());

        $s->collect(0, 5);
        $this->assertSame(5, $s->collectionTimestamp());
    }

    public function test_synchronous_collection_timestamp_returns_last_collection_timestamp(): void
    {
        $s = new SynchronousMetricStream(null, new SumAggregation(), null, 3);
        $this->assertSame(3, $s->collectionTimestamp());

        $s->collect(0, 5);
        $this->assertSame(5, $s->collectionTimestamp());
    }

    public function test_asynchronous_unregister_removes_reader(): void
    {
        /** @var int|null $value */
        $value = null;

        $s = new AsynchronousMetricStream(Attributes::factory(), null, new SumAggregation(), null, function (ObserverInterface $observer) use (&$value): void {
            if ($value !== null) {
                $observer->observe($value);
            }
        }, 3);

        $value = 5;
        $d = $s->register(Temporality::DELTA);
        $s->collect($d, 5);
        $s->unregister($d);

        // Implementation treats unknown reader as cumulative reader
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create([]), 3, 7),
        ], Temporality::CUMULATIVE, false), $s->collect($d, 7));
    }

    public function test_synchronous_unregister_removes_reader(): void
    {
        $s = new SynchronousMetricStream(null, new SumAggregation(), null, 3);
        $w = new StreamWriter(null, Attributes::factory(), $s->writable());

        $c = $s->register(Temporality::CUMULATIVE);
        $w->record(5, [], null, 4);
        $s->collect($c, 5);
        $s->unregister($c);

        $w->record(-5, [], null, 6);
        $this->assertEquals(new Data\Sum([
        ], Temporality::DELTA, false), $s->collect($c, 7));
    }

    public function test_asynchronous_unregister_invalid_does_not_affect_reader(): void
    {
        /** @var int|null $value */
        $value = null;

        $s = new AsynchronousMetricStream(Attributes::factory(), null, new SumAggregation(), null, function (ObserverInterface $observer) use (&$value): void {
            if ($value !== null) {
                $observer->observe($value);
            }
        }, 3);

        $value = 5;
        $d = $s->register(Temporality::DELTA);
        $s->collect($d, 5);
        $s->unregister($d + 1);

        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(0, Attributes::create([]), 5, 7),
        ], Temporality::DELTA, false), $s->collect($d, 7));
    }

    public function test_synchronous_unregister_invalid_does_not_affect_reader(): void
    {
        $s = new SynchronousMetricStream(null, new SumAggregation(), null, 3);
        $w = new StreamWriter(null, Attributes::factory(), $s->writable());

        $c = $s->register(Temporality::CUMULATIVE);
        $w->record(5, [], null, 4);
        $s->collect($c, 5);
        $s->unregister($c + 1);

        $w->record(-5, [], null, 6);
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(0, Attributes::create([]), 3, 7),
        ], Temporality::CUMULATIVE, false), $s->collect($c, 7));
    }

    public function test_synchronous_reader_limit_exceeded_triggers_warning(): void
    {
        if (extension_loaded('gmp')) {
            $this->markTestSkipped();
        }

        $s = new SynchronousMetricStream(null, new SumAggregation(), null, 3);

        for ($i = 0; $i < (PHP_INT_SIZE << 3) - 1; $i++) {
            $s->register(Temporality::DELTA);
        }

        $this->expectException(PHPUnitFrameworkException::class);
        $this->expectExceptionMessageMatches('/^GMP extension required to register over \d++ readers$/');
        $s->register(Temporality::DELTA);
    }

    public function test_synchronous_reader_limit_exceeded_returns_noop_reader(): void
    {
        if (extension_loaded('gmp')) {
            $this->markTestSkipped();
        }

        $s = new SynchronousMetricStream(null, new SumAggregation(), null, 3);
        $w = new StreamWriter(null, Attributes::factory(), $s->writable());

        for ($i = 0; $i < (PHP_INT_SIZE << 3) - 1; $i++) {
            $s->register(Temporality::DELTA);
        }

        $d = @$s->register(Temporality::DELTA);

        $w->record(5, [], null, 4);
        $this->assertEquals(new Data\Sum([
        ], Temporality::DELTA, false), $s->collect($d, 5));
    }

    public function test_synchronous_reader_limit_does_not_apply_if_gmp_available(): void
    {
        if (!extension_loaded('gmp')) {
            $this->markTestSkipped();
        }

        $s = new SynchronousMetricStream(null, new SumAggregation(), null, 3);
        $w = new StreamWriter(null, Attributes::factory(), $s->writable());

        for ($i = 0; $i < (PHP_INT_SIZE << 3) - 1; $i++) {
            $s->register(Temporality::DELTA);
        }

        $d = $s->register(Temporality::DELTA);

        $w->record(5, [], null, 4);
        $this->assertEquals(new Data\Sum([
            new Data\NumberDataPoint(5, Attributes::create([]), 3, 5),
        ], Temporality::DELTA, false), $s->collect($d, 5));
    }

    public function test_metric_aggregator_applies_attribute_filter(): void
    {
        $aggregator = new MetricAggregator(new FilteredAttributeProcessor(Attributes::factory(), ['foo', 'bar']), new SumAggregation(), null);
        $aggregator->record(5, Attributes::create(['foo' => 1, 'bar' => 2, 'baz' => 3]), Context::getRoot(), 0);

        $this->assertEquals(
            Attributes::create(['foo' => 1, 'bar' => 2]),
            current($aggregator->collect(1)->attributes),
        );
    }

    public function test_metric_aggregator_forwards_to_exemplar_filter(): void
    {
        $exemplarReservoir = $this->createMock(ExemplarReservoirInterface::class);
        $exemplarReservoir->expects($this->once())->method('offer')->with($this->anything(), 5, Attributes::create(['foo' => 1]), Context::getRoot(), 3, 0);
        $aggregator = new MetricAggregator(new FilteredAttributeProcessor(Attributes::factory(), ['foo', 'bar']), new SumAggregation(), $exemplarReservoir);
        $aggregator->record(5, Attributes::create(['foo' => 1]), Context::getRoot(), 3);
    }

    public function test_metric_aggregator_exemplars_provides_current_revision_range(): void
    {
        $exemplars = [
            [new Data\Exemplar(5, 3, Attributes::create([]), null, null)],
        ];
        $exemplarReservoir = $this->createMock(ExemplarReservoirInterface::class);
        $exemplarReservoir->expects($this->once())->method('collect')->with($this->anything(), 0, 1)->willReturn($exemplars);
        $aggregator = new MetricAggregator(new FilteredAttributeProcessor(Attributes::factory(), ['foo', 'bar']), new SumAggregation(), $exemplarReservoir);
        $aggregator->record(5, Attributes::create([]), Context::getRoot(), 3);
        $metric = $aggregator->collect(2);

        $this->assertSame($exemplars, $aggregator->exemplars($metric));
    }
}
