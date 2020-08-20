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
    public function testMeter()
    {
        $meter = new Meter('Meter', '0.1');

        $this->assertSame('Meter', $meter->getName());
        $this->assertSame('0.1', $meter->getVersion());
    }

    public function testMeterCounter()
    {
        $meter = new Meter('Meter', '0.1');

        $counterName = 'Counter';
        $counterDescription = 'A counter';
        $counter = $meter->newCounter($counterName, $counterDescription);
        $this->assertInstanceOf(Counter::class, $counter);
        $this->assertEquals($counterName, $counter->getName());
        $this->assertEquals($counterDescription, $counter->getDescription());
    }

    public function testMeterUpDownCounter()
    {
        $meter = new Meter('Meter', '0.1');

        $upDownCounterName = 'Updowncounter';
        $upDownCounterDescription = 'An up/down counter';
        $upDownCounter = $meter->newUpDownCounter($upDownCounterName, $upDownCounterDescription);
        $this->assertInstanceOf(UpDownCounter::class, $upDownCounter);
        $this->assertEquals($upDownCounterName, $upDownCounter->getName());
        $this->assertEquals($upDownCounterDescription, $upDownCounter->getDescription());
    }

    public function testMeterValueRecorder()
    {
        $meter = new Meter('Meter', '0.1');

        $valueRecorderName = 'ValueRecorder';
        $valueRecorderDescription = 'A value recorder';
        $valueRecorder = $meter->newValueRecorder($valueRecorderName, $valueRecorderDescription);
        $this->assertInstanceOf(ValueRecorder::class, $valueRecorder);
        $this->assertEquals($valueRecorderName, $valueRecorder->getName());
        $this->assertEquals($valueRecorderDescription, $valueRecorder->getDescription());
    }
}
