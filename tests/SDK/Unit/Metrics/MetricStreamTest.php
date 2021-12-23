<?php declare(strict_types=1);
namespace OpenTelemetry\Tests\SDK\Unit\Metrics;

use OpenTelemetry\Metrics\Observer;
use OpenTelemetry\SDK\AttributesFactory;
use OpenTelemetry\SDK\Clock;
use OpenTelemetry\SDK\Metrics\Aggregation\Sum;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricWriter\StreamWriter;
use OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricStream;
use OpenTelemetry\SDK\Trace\SystemClock;
use PHPUnit\Framework\TestCase;

final class MetricStreamTest extends TestCase {

    public function testAsynchronousSingleDataPoint(): void {
        $s = new AsynchronousMetricStream(new AttributesFactory(), null, new Sum(), null, function(Observer $observer) use (&$m): void {
            match ($m) {
                0 => $observer->observe(5),
                1 => $observer->observe(7),
                2 => $observer->observe(3),
            };
        }, 3);

        $d = $s->register(Temporality::Delta);
        $c = $s->register(Temporality::Cumulative);

        $m = 0;
        $md = $s->collect($d, 5);
        $mc = $s->collect($c, 5);
        $this->assertSame(3, $md->dataPoints[0]->startTimestamp);
        $this->assertSame(3, $mc->dataPoints[0]->startTimestamp);
        $this->assertSame(5, $md->dataPoints[0]->timestamp);
        $this->assertSame(5, $mc->dataPoints[0]->timestamp);
        $this->assertSame(5, $md->dataPoints[0]->value);
        $this->assertSame(5, $mc->dataPoints[0]->value);
        $this->assertSame(Temporality::Delta, $md->temporality);
        $this->assertSame(Temporality::Cumulative, $mc->temporality);

        $m = 1;
        $md = $s->collect($d, 8);
        $mc = $s->collect($c, 8);
        $this->assertSame(5, $md->dataPoints[0]->startTimestamp);
        $this->assertSame(3, $mc->dataPoints[0]->startTimestamp);
        $this->assertSame(8, $md->dataPoints[0]->timestamp);
        $this->assertSame(8, $mc->dataPoints[0]->timestamp);
        $this->assertSame(2, $md->dataPoints[0]->value);
        $this->assertSame(7, $mc->dataPoints[0]->value);
        $this->assertSame(Temporality::Delta, $md->temporality);
        $this->assertSame(Temporality::Cumulative, $mc->temporality);

        $m = 2;
        $md = $s->collect($d, 12);
        $mc = $s->collect($c, 12);
        $this->assertSame(8, $md->dataPoints[0]->startTimestamp);
        $this->assertSame(3, $mc->dataPoints[0]->startTimestamp);
        $this->assertSame(12, $md->dataPoints[0]->timestamp);
        $this->assertSame(12, $mc->dataPoints[0]->timestamp);
        $this->assertSame(-4, $md->dataPoints[0]->value);
        $this->assertSame(3, $mc->dataPoints[0]->value);
        $this->assertSame(Temporality::Delta, $md->temporality);
        $this->assertSame(Temporality::Cumulative, $mc->temporality);
    }

    public function testSynchronousSingleDataPoint(): void {
        $s = new SynchronousMetricStream(null, new Sum(), null, 3);
        $w = new StreamWriter($s->writable(), new AttributesFactory(), new Clock(SystemClock::getInstance()));

        $d = $s->register(Temporality::Delta);
        $c = $s->register(Temporality::Cumulative);

        $w->record(5);
        $md = $s->collect($d, 5);
        $mc = $s->collect($c, 5);
        $this->assertSame(3, $md->dataPoints[0]->startTimestamp);
        $this->assertSame(3, $mc->dataPoints[0]->startTimestamp);
        $this->assertSame(5, $md->dataPoints[0]->timestamp);
        $this->assertSame(5, $mc->dataPoints[0]->timestamp);
        $this->assertSame(5, $md->dataPoints[0]->value);
        $this->assertSame(5, $mc->dataPoints[0]->value);
        $this->assertSame(Temporality::Delta, $md->temporality);
        $this->assertSame(Temporality::Cumulative, $mc->temporality);

        $w->record(2);
        $md = $s->collect($d, 8);
        $mc = $s->collect($c, 8);
        $this->assertSame(5, $md->dataPoints[0]->startTimestamp);
        $this->assertSame(3, $mc->dataPoints[0]->startTimestamp);
        $this->assertSame(8, $md->dataPoints[0]->timestamp);
        $this->assertSame(8, $mc->dataPoints[0]->timestamp);
        $this->assertSame(2, $md->dataPoints[0]->value);
        $this->assertSame(7, $mc->dataPoints[0]->value);
        $this->assertSame(Temporality::Delta, $md->temporality);
        $this->assertSame(Temporality::Cumulative, $mc->temporality);

        $w->record(-4);
        $md = $s->collect($d, 12);
        $mc = $s->collect($c, 12);
        $this->assertSame(8, $md->dataPoints[0]->startTimestamp);
        $this->assertSame(3, $mc->dataPoints[0]->startTimestamp);
        $this->assertSame(12, $md->dataPoints[0]->timestamp);
        $this->assertSame(12, $mc->dataPoints[0]->timestamp);
        $this->assertSame(-4, $md->dataPoints[0]->value);
        $this->assertSame(3, $mc->dataPoints[0]->value);
        $this->assertSame(Temporality::Delta, $md->temporality);
        $this->assertSame(Temporality::Cumulative, $mc->temporality);
    }
}
