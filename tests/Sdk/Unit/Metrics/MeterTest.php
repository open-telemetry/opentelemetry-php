<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Metrics;

use OpenTelemetry\Sdk\Metrics\Counter;
use OpenTelemetry\Sdk\Metrics\Meter;
use OpenTelemetry\Sdk\Metrics\UpDownCounter;
use OpenTelemetry\Sdk\Metrics\ValueRecorder;
use PHPUnit\Framework\TestCase;

class MeterTest extends TestCase
{
    public function testMeterInstrumentCreation()
    {
        $meter = new Meter('Meter', '0.1');
        $this->assertInstanceOf(Meter::class, $meter);

        $meter_name = $meter->getName();
        $this->assertSame('Meter', $meter_name);

        $meter_version = $meter->getVersion();
        $this->assertSame('0.1', $meter_version);

        $counter = $meter->newCounter('Counter', 'A counter');
        $this->assertInstanceOf(Counter::class, $counter);

        $upDownCounter = $meter->newUpDownCounter('Updowncounter', 'An up/down counter');
        $this->assertInstanceOf(UpDownCounter::class, $upDownCounter);

        $valueRecorder = $meter->newValueRecorder('ValueRecorder', 'A value recorder');
        $this->assertInstanceOf(ValueRecorder::class, $valueRecorder);
    }
}
