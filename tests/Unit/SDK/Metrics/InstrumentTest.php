<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\API\Metrics\Observer;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\Aggregation;
use OpenTelemetry\SDK\Metrics\Data\Histogram;
use OpenTelemetry\SDK\Metrics\Data\HistogramDataPoint;
use OpenTelemetry\SDK\Metrics\Data\NumberDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Sum;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricObserver\MultiObserver;
use OpenTelemetry\SDK\Metrics\SdkCounter;
use OpenTelemetry\SDK\Metrics\SdkHistogram;
use OpenTelemetry\SDK\Metrics\SdkObservableCounter;
use OpenTelemetry\SDK\Metrics\SdkUpDownCounter;
use OpenTelemetry\SDK\Metrics\StalenessHandler\NoopStalenessHandler;
use OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\Stream\StreamWriter;
use OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricStream;
use PHPUnit\Framework\TestCase;

final class InstrumentTest extends TestCase
{

    /**
     * @covers \OpenTelemetry\SDK\Metrics\SdkCounter
     */
    public function test_counter(): void
    {
        $s = new SynchronousMetricStream(null, new Aggregation\Sum(true), null, 0);
        $c = new SdkCounter(new StreamWriter(null, Attributes::factory(), $s->writable()), new NoopStalenessHandler(), ClockFactory::getDefault());
        $r = $s->register(Temporality::DELTA);

        $c->add(5);
        $c->add(7);
        $c->add(3);

        $this->assertEquals(new Sum(
            [
                new NumberDataPoint(
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
     * @covers \OpenTelemetry\SDK\Metrics\SdkObservableCounter
     */
    public function test_asynchronous_counter(): void
    {
        $o = new MultiObserver(new NoopStalenessHandler());
        $s = new AsynchronousMetricStream(Attributes::factory(), null, new Aggregation\Sum(true), null, $o, 0);
        $c = new SdkObservableCounter($o, new NoopStalenessHandler());
        $r = $s->register(Temporality::CUMULATIVE);

        $c->observe(static function (Observer $observer): void {
            $observer->observe(5);
        });

        $this->assertEquals(new Sum(
            [
                new NumberDataPoint(
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
     * @covers \OpenTelemetry\SDK\Metrics\SdkUpDownCounter
     */
    public function test_up_down_counter(): void
    {
        $s = new SynchronousMetricStream(null, new Aggregation\Sum(false), null, 0);
        $c = new SdkUpDownCounter(new StreamWriter(null, Attributes::factory(), $s->writable()), new NoopStalenessHandler(), ClockFactory::getDefault());
        $r = $s->register(Temporality::DELTA);

        $c->add(5);
        $c->add(7);
        $c->add(-8);

        $this->assertEquals(new Sum(
            [
                new NumberDataPoint(
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
     * @covers \OpenTelemetry\SDK\Metrics\SdkHistogram
     */
    public function test_histogram(): void
    {
        $s = new SynchronousMetricStream(null, new Aggregation\ExplicitBucketHistogram([3, 6, 9]), null, 0);
        $h = new SdkHistogram(new StreamWriter(null, Attributes::factory(), $s->writable()), new NoopStalenessHandler(), ClockFactory::getDefault());
        $r = $s->register(Temporality::DELTA);

        $h->record(1);
        $h->record(7);
        $h->record(9);
        $h->record(12);
        $h->record(15);
        $h->record(8);
        $h->record(7);

        $this->assertEquals(new Histogram(
            [
                new HistogramDataPoint(
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
