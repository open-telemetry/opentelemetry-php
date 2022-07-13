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
use OpenTelemetry\SDK\Metrics\MetricObserver\MultiObserver;
use OpenTelemetry\SDK\Metrics\ObservableCounter;
use OpenTelemetry\SDK\Metrics\StalenessHandler\NoopStalenessHandler;
use OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricStream;
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
        $s = new SynchronousMetricStream(null, new SumAggregation(true), null, 0);
        $c = new Counter(new StreamWriter(null, Attributes::factory(), $s->writable()), new NoopStalenessHandler(), ClockFactory::getDefault());
        $r = $s->register(Temporality::DELTA);

        $c->add(5);
        $c->add(7);
        $c->add(3);

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
        ), $s->collect($r, 1));
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter
     */
    public function test_asynchronous_counter(): void
    {
        $o = new MultiObserver();
        $s = new AsynchronousMetricStream(Attributes::factory(), null, new SumAggregation(true), null, $o, 0);
        $c = new ObservableCounter($o, new NoopStalenessHandler());
        $r = $s->register(Temporality::CUMULATIVE);

        $c->observe(static function (ObserverInterface $observer): void {
            $observer->observe(5);
        });

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
        ), $s->collect($r, 1));
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\UpDownCounter
     */
    public function test_up_down_counter(): void
    {
        $s = new SynchronousMetricStream(null, new SumAggregation(false), null, 0);
        $c = new UpDownCounter(new StreamWriter(null, Attributes::factory(), $s->writable()), new NoopStalenessHandler(), ClockFactory::getDefault());
        $r = $s->register(Temporality::DELTA);

        $c->add(5);
        $c->add(7);
        $c->add(-8);

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
        ), $s->collect($r, 1));
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\Histogram
     */
    public function test_histogram(): void
    {
        $s = new SynchronousMetricStream(null, new ExplicitBucketHistogramAggregation([3, 6, 9]), null, 0);
        $h = new Histogram(new StreamWriter(null, Attributes::factory(), $s->writable()), new NoopStalenessHandler(), ClockFactory::getDefault());
        $r = $s->register(Temporality::DELTA);

        $h->record(1);
        $h->record(7);
        $h->record(9);
        $h->record(12);
        $h->record(15);
        $h->record(8);
        $h->record(7);

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
        ), $s->collect($r, 1));
    }
}
