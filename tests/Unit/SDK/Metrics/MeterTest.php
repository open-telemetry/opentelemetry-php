<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Counter;
use OpenTelemetry\SDK\Metrics\Providers\MeterProvider;
use OpenTelemetry\SDK\Metrics\UpDownCounter;
use OpenTelemetry\SDK\Metrics\ValueRecorder;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Metrics\Meter
 */
class MeterTest extends TestCase
{
    public function test_meter(): void
    {
        $meter = (new MeterProvider())->getMeter('Meter', '0.1');

        $this->assertSame('Meter', $meter->getInstrumentationScope()->getName());
        $this->assertSame('0.1', $meter->getInstrumentationScope()->getVersion());
    }

    public function test_meter_counter(): void
    {
        $meter = (new MeterProvider())->getMeter('Meter', '0.1');

        $counterName = 'Counter';
        $counterDescription = 'A counter';
        $counter = $meter->newCounter($counterName, $counterDescription);
        $this->assertInstanceOf(Counter::class, $counter);
        $this->assertEquals($counterName, $counter->getName());
        $this->assertEquals($counterDescription, $counter->getDescription());
    }

    public function test_meter_up_down_counter(): void
    {
        $meter = (new MeterProvider())->getMeter('Meter', '0.1');

        $upDownCounterName = 'Updowncounter';
        $upDownCounterDescription = 'An up/down counter';
        $upDownCounter = $meter->newUpDownCounter($upDownCounterName, $upDownCounterDescription);
        $this->assertInstanceOf(UpDownCounter::class, $upDownCounter);
        $this->assertEquals($upDownCounterName, $upDownCounter->getName());
        $this->assertEquals($upDownCounterDescription, $upDownCounter->getDescription());
    }

    public function test_meter_value_recorder(): void
    {
        $meter = (new MeterProvider())->getMeter('Meter', '0.1');

        $valueRecorderName = 'ValueRecorder';
        $valueRecorderDescription = 'A value recorder';
        $valueRecorder = $meter->newValueRecorder($valueRecorderName, $valueRecorderDescription);
        $this->assertInstanceOf(ValueRecorder::class, $valueRecorder);
        $this->assertEquals($valueRecorderName, $valueRecorder->getName());
        $this->assertEquals($valueRecorderDescription, $valueRecorder->getDescription());
    }
}
