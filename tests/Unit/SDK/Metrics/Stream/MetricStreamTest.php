<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Stream;

use OpenTelemetry\API\Metrics\Observer;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Aggregation;
use OpenTelemetry\SDK\Metrics\Data;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\Stream\StreamWriter;
use OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricStream;
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
 * @uses \OpenTelemetry\SDK\Metrics\Aggregation\Sum
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

        $s = new AsynchronousMetricStream(Attributes::factory(), null, new Aggregation\Sum(), null, function (Observer $observer) use (&$value): void {
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

        $s = new AsynchronousMetricStream(Attributes::factory(), null, new Aggregation\Sum(), null, function (Observer $observer) use (&$m): void {
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

        $s = new AsynchronousMetricStream(Attributes::factory(), null, new Aggregation\Sum(), null, function (Observer $observer) use (&$value): void {
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
        $s = new SynchronousMetricStream(null, new Aggregation\Sum(), null, 3);
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
        $s = new SynchronousMetricStream(null, new Aggregation\Sum(), null, 3);
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
}
