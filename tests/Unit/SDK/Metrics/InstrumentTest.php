<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\API\Common\Time\TestClock;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Aggregation\ExplicitBucketHistogramAggregation;
use OpenTelemetry\SDK\Metrics\Aggregation\SumAggregation;
use OpenTelemetry\SDK\Metrics\Counter;
use OpenTelemetry\SDK\Metrics\Data;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\Histogram;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricRegistry;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;
use OpenTelemetry\SDK\Metrics\ObservableCallback;
use OpenTelemetry\SDK\Metrics\ObservableCallbackDestructor;
use OpenTelemetry\SDK\Metrics\ObservableCounter;
use OpenTelemetry\SDK\Metrics\ObservableInstrumentTrait;
use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandler\NoopStalenessHandler;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregator;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregatorFactory;
use OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\UpDownCounter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use WeakMap;

#[CoversClass(Counter::class)]
#[CoversClass(ObservableCounter::class)]
#[CoversClass(UpDownCounter::class)]
#[CoversClass(Histogram::class)]
#[CoversClass(ObservableCallback::class)]
#[CoversClass(ObservableInstrumentTrait::class)]
final class InstrumentTest extends TestCase
{
    public function test_counter(): void
    {
        $a = new MetricAggregator(null, new SumAggregation(true));
        $s = new SynchronousMetricStream(new SumAggregation(true), 0);
        $w = new MetricRegistry(null, Attributes::factory(), new TestClock(1));
        $i = new Instrument(InstrumentType::COUNTER, 'test', null, null);
        $n = $w->registerSynchronousStream($i, $s, $a);
        $r = $s->register(Temporality::DELTA);

        $c = new Counter($w, $i, new NoopStalenessHandler());
        $c->add(5);
        $c->add(7);
        $c->add(3);

        $w->collectAndPush([$n]);
        $this->assertEquals(new Data\Sum(
            [
                new Data\NumberDataPoint(
                    15,
                    Attributes::create([]),
                    0,
                    1,
                ),
            ],
            Temporality::DELTA,
            true,
        ), $s->collect($r));
    }

    public function test_asynchronous_counter(): void
    {
        $a = new MetricAggregatorFactory(null, new SumAggregation(true));
        $s = new SynchronousMetricStream(new SumAggregation(true), 0);
        $w = new MetricRegistry(null, Attributes::factory(), new TestClock(1));
        $i = new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, 'test', null, null);
        $n = $w->registerAsynchronousStream($i, $s, $a);
        $r = $s->register(Temporality::CUMULATIVE);

        $c = new ObservableCounter($w, $i, new NoopStalenessHandler(), new WeakMap());
        $c->observe(static function (ObserverInterface $observer): void {
            $observer->observe(5);
        });

        $w->collectAndPush([$n]);
        $this->assertEquals(new Data\Sum(
            [
                new Data\NumberDataPoint(
                    5,
                    Attributes::create([]),
                    0,
                    1,
                ),
            ],
            Temporality::CUMULATIVE,
            true,
        ), $s->collect($r));
    }

    public function test_asynchronous_counter_weaken(): void
    {
        $a = new MetricAggregatorFactory(null, new SumAggregation(true));
        $s = new SynchronousMetricStream(new SumAggregation(true), 0);
        $w = new MetricRegistry(null, Attributes::factory(), new TestClock(1));
        $i = new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, 'test', null, null);
        $n = $w->registerAsynchronousStream($i, $s, $a);
        $r = $s->register(Temporality::CUMULATIVE);

        $instance = new class() {
            public function __invoke(ObserverInterface $observer)
            {
                $observer->observe(5);
            }
        };

        $c = new ObservableCounter($w, $i, new NoopStalenessHandler(), new WeakMap());
        $c->observe($instance);
        $instance = null;

        $w->collectAndPush([$n]);
        $this->assertEquals(new Data\Sum(
            [],
            Temporality::CUMULATIVE,
            true,
        ), $s->collect($r));
    }

    public function test_up_down_counter(): void
    {
        $a = new MetricAggregator(null, new SumAggregation(false));
        $s = new SynchronousMetricStream(new SumAggregation(false), 0);
        $w = new MetricRegistry(null, Attributes::factory(), new TestClock(1));
        $i = new Instrument(InstrumentType::UP_DOWN_COUNTER, 'test', null, null);
        $n = $w->registerSynchronousStream($i, $s, $a);
        $r = $s->register(Temporality::DELTA);

        $c = new UpDownCounter($w, $i, new NoopStalenessHandler());
        $c->add(5);
        $c->add(7);
        $c->add(-8);

        $w->collectAndPush([$n]);
        $this->assertEquals(new Data\Sum(
            [
                new Data\NumberDataPoint(
                    4,
                    Attributes::create([]),
                    0,
                    1,
                ),
            ],
            Temporality::DELTA,
            false,
        ), $s->collect($r));
    }

    public function test_histogram(): void
    {
        $a = new MetricAggregator(null, new ExplicitBucketHistogramAggregation([3, 6, 9]));
        $s = new SynchronousMetricStream(new ExplicitBucketHistogramAggregation([3, 6, 9]), 0);
        $w = new MetricRegistry(null, Attributes::factory(), new TestClock(1));
        $i = new Instrument(InstrumentType::HISTOGRAM, 'test', null, null);
        $n = $w->registerSynchronousStream($i, $s, $a);
        $r = $s->register(Temporality::DELTA);

        $h = new Histogram($w, $i, new NoopStalenessHandler());
        $h->record(1);
        $h->record(7);
        $h->record(9);
        $h->record(12);
        $h->record(15);
        $h->record(8);
        $h->record(7);

        $w->collectAndPush([$n]);
        $this->assertEquals(new Data\Histogram(
            [
                new Data\HistogramDataPoint(
                    7,
                    59,
                    1,
                    15,
                    [1, 0, 4, 2],
                    [3, 6, 9],
                    Attributes::create([]),
                    0,
                    1,
                ),
            ],
            Temporality::DELTA,
        ), $s->collect($r));
    }

    public function test_observable_callback_releases_on_detach(): void
    {
        $writer = $this->createMock(MetricWriterInterface::class);
        $writer->expects($this->once())->method('unregisterCallback')->with(1);
        $referenceCounter = $this->createMock(ReferenceCounterInterface::class);
        $referenceCounter->expects($this->once())->method('release');

        $callback = new ObservableCallback($writer, $referenceCounter, 1, null, null);
        $callback->detach();
    }

    public function test_observable_callback_removes_callback_destructor_token_on_detach(): void
    {
        $writer = $this->createMock(MetricWriterInterface::class);
        $referenceCounter = $this->createMock(ReferenceCounterInterface::class);

        $callbackDestructor = new ObservableCallbackDestructor(new WeakMap(), $writer);
        $callbackDestructor->callbackIds[1] = $referenceCounter;

        $callback = new ObservableCallback($writer, $referenceCounter, 1, $callbackDestructor, new stdClass());
        $callback->detach();

        $this->assertArrayNotHasKey(1, $callbackDestructor->callbackIds);
    }

    public function test_observable_callback_acquires_persistent_on_destruct(): void
    {
        $writer = $this->createMock(MetricWriterInterface::class);
        $referenceCounter = $this->createMock(ReferenceCounterInterface::class);
        $referenceCounter->expects($this->once())->method('acquire')->with(true);
        $referenceCounter->expects($this->once())->method('release');

        /** @noinspection PhpExpressionResultUnusedInspection */
        new ObservableCallback($writer, $referenceCounter, 1, null, null);
    }

    public function test_observable_callback_does_not_acquire_persistent_on_destruct_if_callback_destructor_set(): void
    {
        $writer = $this->createMock(MetricWriterInterface::class);
        $referenceCounter = $this->createMock(ReferenceCounterInterface::class);
        $referenceCounter->expects($this->never())->method('acquire')->with(true);

        $callbackDestructor = new ObservableCallbackDestructor(new WeakMap(), $writer);
        $callbackDestructor->callbackIds[1] = $referenceCounter;

        /** @noinspection PhpExpressionResultUnusedInspection */
        new ObservableCallback($writer, $referenceCounter, 1, $callbackDestructor, new stdClass());
    }

    public function test_synchronous_disabled_if_meter_disabled(): void
    {
        $w = $this->createMock(MetricWriterInterface::class);
        $c = $this->createMock(ReferenceCounterInterface::class);
        $i = new Instrument(InstrumentType::UP_DOWN_COUNTER, 'test', null, null);
        $w->expects($this->once())->method('enabled')->with($i)->willReturn(false);
        $counter = new Counter($w, $i, $c);

        $this->assertFalse($counter->isEnabled());
    }

    public function test_asynchronous_disabled_if_meter_disabled(): void
    {
        $w = $this->createMock(MetricWriterInterface::class);
        $c = $this->createMock(ReferenceCounterInterface::class);
        $i = new Instrument(InstrumentType::UP_DOWN_COUNTER, 'test', null, null);
        $w->expects($this->once())->method('enabled')->with($i)->willReturn(false);
        $counter = new ObservableCounter($w, $i, $c, new WeakMap());

        $this->assertFalse($counter->isEnabled());
    }
}
