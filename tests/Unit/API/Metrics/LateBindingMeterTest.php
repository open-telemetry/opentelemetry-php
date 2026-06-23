<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Metrics;

use OpenTelemetry\API\Metrics\AsynchronousInstrument;
use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\GaugeInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;
use OpenTelemetry\API\Metrics\LateBindingMeter;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\API\Metrics\ObservableCounterInterface;
use OpenTelemetry\API\Metrics\ObservableGaugeInterface;
use OpenTelemetry\API\Metrics\ObservableUpDownCounterInterface;
use OpenTelemetry\API\Metrics\UpDownCounterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LateBindingMeter::class)]
class LateBindingMeterTest extends TestCase
{
    public function test_create_counter_delegates_to_meter(): void
    {
        $counter = $this->createMock(CounterInterface::class);
        $meter = $this->createMock(MeterInterface::class);
        $meter->expects($this->once())->method('createCounter')->with('test', 'unit', 'desc', [])->willReturn($counter);

        $lateBinding = new LateBindingMeter(fn () => $meter);
        $this->assertSame($counter, $lateBinding->createCounter('test', 'unit', 'desc'));
    }

    public function test_create_histogram_delegates_to_meter(): void
    {
        $histogram = $this->createMock(HistogramInterface::class);
        $meter = $this->createMock(MeterInterface::class);
        $meter->expects($this->once())->method('createHistogram')->willReturn($histogram);

        $lateBinding = new LateBindingMeter(fn () => $meter);
        $this->assertSame($histogram, $lateBinding->createHistogram('test'));
    }

    public function test_create_gauge_delegates_to_meter(): void
    {
        $gauge = $this->createMock(GaugeInterface::class);
        $meter = $this->createMock(MeterInterface::class);
        $meter->expects($this->once())->method('createGauge')->willReturn($gauge);

        $lateBinding = new LateBindingMeter(fn () => $meter);
        $this->assertSame($gauge, $lateBinding->createGauge('test'));
    }

    public function test_create_up_down_counter_delegates_to_meter(): void
    {
        $counter = $this->createMock(UpDownCounterInterface::class);
        $meter = $this->createMock(MeterInterface::class);
        $meter->expects($this->once())->method('createUpDownCounter')->willReturn($counter);

        $lateBinding = new LateBindingMeter(fn () => $meter);
        $this->assertSame($counter, $lateBinding->createUpDownCounter('test'));
    }

    public function test_create_observable_counter_delegates_to_meter(): void
    {
        $counter = $this->createMock(ObservableCounterInterface::class);
        $meter = $this->createMock(MeterInterface::class);
        $meter->expects($this->once())->method('createObservableCounter')->willReturn($counter);

        $lateBinding = new LateBindingMeter(fn () => $meter);
        $this->assertSame($counter, $lateBinding->createObservableCounter('test'));
    }

    public function test_create_observable_gauge_delegates_to_meter(): void
    {
        $gauge = $this->createMock(ObservableGaugeInterface::class);
        $meter = $this->createMock(MeterInterface::class);
        $meter->expects($this->once())->method('createObservableGauge')->willReturn($gauge);

        $lateBinding = new LateBindingMeter(fn () => $meter);
        $this->assertSame($gauge, $lateBinding->createObservableGauge('test'));
    }

    public function test_create_observable_up_down_counter_delegates_to_meter(): void
    {
        $counter = $this->createMock(ObservableUpDownCounterInterface::class);
        $meter = $this->createMock(MeterInterface::class);
        $meter->expects($this->once())->method('createObservableUpDownCounter')->willReturn($counter);

        $lateBinding = new LateBindingMeter(fn () => $meter);
        $this->assertSame($counter, $lateBinding->createObservableUpDownCounter('test'));
    }

    public function test_batch_observe_delegates_to_meter(): void
    {
        $callback = $this->createMock(ObservableCallbackInterface::class);
        $instrument = $this->createMock(AsynchronousInstrument::class);
        $meter = $this->createMock(MeterInterface::class);
        $meter->expects($this->once())->method('batchObserve')->willReturn($callback);

        $lateBinding = new LateBindingMeter(fn () => $meter);
        $this->assertSame($callback, $lateBinding->batchObserve(fn () => null, $instrument));
    }

    public function test_factory_called_only_once(): void
    {
        $callCount = 0;
        $meter = $this->createMock(MeterInterface::class);
        $meter->method('createCounter')->willReturn($this->createMock(CounterInterface::class));
        $meter->method('createHistogram')->willReturn($this->createMock(HistogramInterface::class));

        $lateBinding = new LateBindingMeter(function () use ($meter, &$callCount) {
            $callCount++;
            return $meter;
        });

        $lateBinding->createCounter('a');
        $lateBinding->createHistogram('b');
        $this->assertSame(1, $callCount);
    }
}
