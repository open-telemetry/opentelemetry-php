<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\Aggregation\ExplicitBucketHistogramAggregation;
use OpenTelemetry\SDK\Metrics\Aggregation\SumAggregation;
use OpenTelemetry\SDK\Metrics\Counter;
use OpenTelemetry\SDK\Metrics\Data;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\Histogram;
use OpenTelemetry\SDK\Metrics\MetricObserver\CallbackDestructor;
use OpenTelemetry\SDK\Metrics\MetricObserver\MultiObserver;
use OpenTelemetry\SDK\Metrics\MetricObserverInterface;
use OpenTelemetry\SDK\Metrics\ObservableCallback;
use OpenTelemetry\SDK\Metrics\ObservableCounter;
use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandler\NoopStalenessHandler;
use OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricCollector;
use OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregator;
use OpenTelemetry\SDK\Metrics\Stream\StreamWriter;
use OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\UpDownCounter;
use PHPUnit\Framework\TestCase;

final class InstrumentTest extends TestCase
{

    /**
     * @covers \OpenTelemetry\SDK\Metrics\Counter
     */
    public function test_counter(): void
    {
        $a = new MetricAggregator(null, new SumAggregation(true));
        $s = new SynchronousMetricStream(new SumAggregation(true), 0);
        $c = new Counter(new StreamWriter(null, Attributes::factory(), $a), new NoopStalenessHandler(), ClockFactory::getDefault());
        $r = $s->register(Temporality::DELTA);

        $c->add(5);
        $c->add(7);
        $c->add(3);

        $s->push($a->collect(1));
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

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter
     */
    public function test_asynchronous_counter(): void
    {
        $o = new MultiObserver();
        $a = new AsynchronousMetricCollector($o, null, new SumAggregation(true), Attributes::factory());
        $s = new AsynchronousMetricStream(new SumAggregation(true), 0);
        $c = new ObservableCounter($o, new NoopStalenessHandler());
        $r = $s->register(Temporality::CUMULATIVE);

        $c->observe(static function (ObserverInterface $observer): void {
            $observer->observe(5);
        });

        $s->push($a->collect(1));
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

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter
     */
    public function test_asynchronous_counter_weaken(): void
    {
        $o = new MultiObserver();
        $a = new AsynchronousMetricCollector($o, null, new SumAggregation(true), Attributes::factory());
        $s = new AsynchronousMetricStream(new SumAggregation(true), 0);
        $c = new ObservableCounter($o, new NoopStalenessHandler());
        $r = $s->register(Temporality::CUMULATIVE);

        $instance = new class() {
            public function __invoke(ObserverInterface $observer)
            {
                $observer->observe(5);
            }
        };

        $c->observe($instance, true);
        $instance = null;

        $s->push($a->collect(1));
        $this->assertEquals(new Data\Sum(
            [],
            Temporality::CUMULATIVE,
            true,
        ), $s->collect($r));
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\UpDownCounter
     */
    public function test_up_down_counter(): void
    {
        $a = new MetricAggregator(null, new SumAggregation(false));
        $s = new SynchronousMetricStream(new SumAggregation(false), 0);
        $c = new UpDownCounter(new StreamWriter(null, Attributes::factory(), $a), new NoopStalenessHandler(), ClockFactory::getDefault());
        $r = $s->register(Temporality::DELTA);

        $c->add(5);
        $c->add(7);
        $c->add(-8);

        $s->push($a->collect(1));
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

    /**
     * @covers \OpenTelemetry\SDK\Metrics\Histogram
     */
    public function test_histogram(): void
    {
        $a = new MetricAggregator(null, new ExplicitBucketHistogramAggregation([3, 6, 9]));
        $s = new SynchronousMetricStream(new ExplicitBucketHistogramAggregation([3, 6, 9]), 0);
        $h = new Histogram(new StreamWriter(null, Attributes::factory(), $a), new NoopStalenessHandler(), ClockFactory::getDefault());
        $r = $s->register(Temporality::DELTA);

        $h->record(1);
        $h->record(7);
        $h->record(9);
        $h->record(12);
        $h->record(15);
        $h->record(8);
        $h->record(7);

        $s->push($a->collect(1));
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

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback
     */
    public function test_observable_callback_releases_on_detach(): void
    {
        $metricObserver = $this->createMock(MetricObserverInterface::class);
        $metricObserver->method('has')->with(1)->willReturnOnConsecutiveCalls(true, false);
        $metricObserver->expects($this->once())->method('cancel')->with(1);
        $referenceCounter = $this->createMock(ReferenceCounterInterface::class);
        $referenceCounter->expects($this->once())->method('release');

        $callback = new ObservableCallback($metricObserver, $referenceCounter, 1, null);
        $callback->detach();
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback
     */
    public function test_observable_callback_removes_callback_destructor_token_on_detach(): void
    {
        $metricObserver = $this->createMock(MetricObserverInterface::class);
        $metricObserver->method('has')->with(1)->willReturnOnConsecutiveCalls(true, false);
        $referenceCounter = $this->createMock(ReferenceCounterInterface::class);

        $callbackDestructor = new CallbackDestructor($metricObserver, $referenceCounter);
        $callbackDestructor->tokens[1] = 1;

        $callback = new ObservableCallback($metricObserver, $referenceCounter, 1, $callbackDestructor);
        $callback->detach();

        $this->assertArrayNotHasKey(1, $callbackDestructor->tokens);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback
     */
    public function test_observable_callback_does_not_release_on_detach_if_invalid_token(): void
    {
        $metricObserver = $this->createMock(MetricObserverInterface::class);
        $metricObserver->method('has')->with(1)->willReturn(false);
        $metricObserver->expects($this->never())->method('cancel')->with(1);
        $referenceCounter = $this->createMock(ReferenceCounterInterface::class);
        $referenceCounter->expects($this->never())->method('release');

        $callback = new ObservableCallback($metricObserver, $referenceCounter, 1, null);
        $callback->detach();
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback
     */
    public function test_observable_callback_acquires_persistent_on_destruct(): void
    {
        $metricObserver = $this->createMock(MetricObserverInterface::class);
        $metricObserver->method('has')->with(1)->willReturn(true);
        $referenceCounter = $this->createMock(ReferenceCounterInterface::class);
        $referenceCounter->expects($this->once())->method('acquire')->with(true);
        $referenceCounter->expects($this->once())->method('release');

        /** @noinspection PhpExpressionResultUnusedInspection */
        new ObservableCallback($metricObserver, $referenceCounter, 1, null);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback
     */
    public function test_observable_callback_does_not_acquire_persistent_on_destruct_if_callback_destructor_set(): void
    {
        $metricObserver = $this->createMock(MetricObserverInterface::class);
        $metricObserver->method('has')->with(1)->willReturn(true);
        $referenceCounter = $this->createMock(ReferenceCounterInterface::class);
        $referenceCounter->expects($this->never())->method('acquire')->with(true);

        $callbackDestructor = new CallbackDestructor($metricObserver, $referenceCounter);
        $callbackDestructor->tokens[1] = 1;

        /** @noinspection PhpExpressionResultUnusedInspection */
        new ObservableCallback($metricObserver, $referenceCounter, 1, $callbackDestructor);
    }
}
