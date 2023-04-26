<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\MetricRegistry;

use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Aggregation\SumAggregation;
use OpenTelemetry\SDK\Metrics\Data\NumberDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Sum;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricRegistry;
use OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregator;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregatorFactory;
use OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricStream;
use OpenTelemetry\Tests\Unit\SDK\Util\TestClock;
use PHPUnit\Framework\TestCase;
use function printf;

/**
 * @covers \OpenTelemetry\SDK\Metrics\MetricRegistry\MetricRegistry
 * @covers \OpenTelemetry\SDK\Metrics\MetricRegistry\MultiObserver
 * @covers \OpenTelemetry\SDK\Metrics\MetricRegistry\NoopObserver
 */
final class MetricRegistryTest extends TestCase
{
    public function test_collect_and_push_recorded_value(): void
    {
        $registry = new MetricRegistry(null, Attributes::factory(), new TestClock(1));
        $stream = new SynchronousMetricStream(new SumAggregation(true), 0);
        $instrument = new Instrument(InstrumentType::COUNTER, 'test', null, null);

        $streamId = $registry->registerSynchronousStream($instrument, $stream, new MetricAggregator(null, new SumAggregation(true)));
        $reader = $stream->register(Temporality::DELTA);

        $registry->record($instrument, 5);

        $registry->collectAndPush([$streamId]);
        $this->assertEquals(new Sum([
            new NumberDataPoint(5, Attributes::create([]), 0, 1),
        ], Temporality::DELTA, true), $stream->collect($reader));
    }

    public function test_collect_and_push_callback_value(): void
    {
        $registry = new MetricRegistry(null, Attributes::factory(), new TestClock(1));
        $stream = new AsynchronousMetricStream(new SumAggregation(true), 0);
        $instrument = new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, 'test', null, null);

        $streamId = $registry->registerAsynchronousStream($instrument, $stream, new MetricAggregatorFactory(null, new SumAggregation(true)));
        $reader = $stream->register(Temporality::CUMULATIVE);

        $registry->registerCallback(fn (ObserverInterface $o) => $o->observe(5), $instrument);

        $registry->collectAndPush([$streamId]);
        $this->assertEquals(new Sum([
            new NumberDataPoint(5, Attributes::create([]), 0, 1),
        ], Temporality::CUMULATIVE, true), $stream->collect($reader));
    }

    public function test_collect_and_push_invokes_requested_callback_only_once(): void
    {
        $this->expectOutputString('0');

        $registry = new MetricRegistry(null, Attributes::factory(), new TestClock());
        $stream0 = new AsynchronousMetricStream(new SumAggregation(true), 0);
        $stream1 = new AsynchronousMetricStream(new SumAggregation(true), 0);
        $instrument = new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, 'test', null, null);

        $streamId0 = $registry->registerAsynchronousStream($instrument, $stream0, new MetricAggregatorFactory(null, new SumAggregation(true)));
        $streamId1 = $registry->registerAsynchronousStream($instrument, $stream1, new MetricAggregatorFactory(null, new SumAggregation(true)));

        $registry->registerCallback(fn (ObserverInterface $o) => printf('0'), $instrument);

        $registry->collectAndPush([$streamId0, $streamId1]);
    }

    public function test_collect_and_push_invokes_only_requested_callbacks(): void
    {
        $this->expectOutputString('0011');

        $registry = new MetricRegistry(null, Attributes::factory(), new TestClock());
        $stream0 = new AsynchronousMetricStream(new SumAggregation(true), 0);
        $stream1 = new AsynchronousMetricStream(new SumAggregation(true), 0);
        $instrument0 = new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, 'test', null, null);
        $instrument1 = new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, 'test', null, null);

        $streamId0 = $registry->registerAsynchronousStream($instrument0, $stream0, new MetricAggregatorFactory(null, new SumAggregation(true)));
        $streamId1 = $registry->registerAsynchronousStream($instrument1, $stream1, new MetricAggregatorFactory(null, new SumAggregation(true)));

        $registry->registerCallback(fn (ObserverInterface $o) => printf('0'), $instrument0);
        $registry->registerCallback(fn (ObserverInterface $o) => printf('1'), $instrument1);

        $registry->collectAndPush([$streamId0]);
        $registry->collectAndPush([$streamId0, $streamId1]);
        $registry->collectAndPush([$streamId1]);
    }

    public function test_collect_and_push_multi_instrument_callback_collects_only_specified_streams(): void
    {
        $clock = new TestClock();
        $registry = new MetricRegistry(null, Attributes::factory(), $clock);
        $stream0 = new AsynchronousMetricStream(new SumAggregation(true), 0);
        $stream1 = new AsynchronousMetricStream(new SumAggregation(true), 0);
        $instrument0 = new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, 'test', null, null);
        $instrument1 = new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, 'test', null, null);

        $streamId0 = $registry->registerAsynchronousStream($instrument0, $stream0, new MetricAggregatorFactory(null, new SumAggregation(true)));
        $streamId1 = $registry->registerAsynchronousStream($instrument1, $stream1, new MetricAggregatorFactory(null, new SumAggregation(true)));
        $reader0 = $stream0->register(Temporality::DELTA);
        $reader1 = $stream1->register(Temporality::DELTA);

        $registry->registerCallback(static function (ObserverInterface $o0, ObserverInterface $o1): void {
            $o0->observe(5);
            $o1->observe(7);
        }, $instrument0, $instrument1);

        $clock->setTime(3);
        $registry->collectAndPush([$streamId0]);
        $this->assertEquals(new Sum([
            new NumberDataPoint(5, Attributes::create([]), 0, 3),
        ], Temporality::DELTA, true), $stream0->collect($reader0));
        $this->assertEquals(new Sum([
        ], Temporality::DELTA, true), $stream1->collect($reader1));

        $clock->setTime(5);
        $registry->collectAndPush([$streamId1]);
        $this->assertEquals(new Sum([
            new NumberDataPoint(0, Attributes::create([]), 3, 3),
        ], Temporality::DELTA, true), $stream0->collect($reader0));
        $this->assertEquals(new Sum([
            new NumberDataPoint(7, Attributes::create([]), 0, 5),
        ], Temporality::DELTA, true), $stream1->collect($reader1));

        $clock->setTime(7);
        $registry->collectAndPush([$streamId0, $streamId1]);
        $this->assertEquals(new Sum([
            new NumberDataPoint(0, Attributes::create([]), 3, 7),
        ], Temporality::DELTA, true), $stream0->collect($reader0));
        $this->assertEquals(new Sum([
            new NumberDataPoint(0, Attributes::create([]), 5, 7),
        ], Temporality::DELTA, true), $stream1->collect($reader1));
    }
}
