<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\AsynchronousInstrument;
use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\GaugeInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\API\Metrics\ObservableCounterInterface;
use OpenTelemetry\API\Metrics\ObservableGaugeInterface;
use OpenTelemetry\API\Metrics\ObservableUpDownCounterInterface;
use OpenTelemetry\API\Metrics\UpDownCounterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopMeter::class)]
class NoopMeterTest extends TestCase
{
    private NoopMeter $meter;

    #[\Override]
    protected function setUp(): void
    {
        $this->meter = new NoopMeter();
    }

    public function test_create_counter(): void
    {
        $this->assertInstanceOf(CounterInterface::class, $this->meter->createCounter('test'));
    }

    public function test_create_histogram(): void
    {
        $this->assertInstanceOf(HistogramInterface::class, $this->meter->createHistogram('test'));
    }

    public function test_create_gauge(): void
    {
        $this->assertInstanceOf(GaugeInterface::class, $this->meter->createGauge('test'));
    }

    public function test_create_up_down_counter(): void
    {
        $this->assertInstanceOf(UpDownCounterInterface::class, $this->meter->createUpDownCounter('test'));
    }

    public function test_create_observable_counter(): void
    {
        $this->assertInstanceOf(ObservableCounterInterface::class, $this->meter->createObservableCounter('test'));
    }

    public function test_create_observable_gauge(): void
    {
        $this->assertInstanceOf(ObservableGaugeInterface::class, $this->meter->createObservableGauge('test'));
    }

    public function test_create_observable_up_down_counter(): void
    {
        $this->assertInstanceOf(ObservableUpDownCounterInterface::class, $this->meter->createObservableUpDownCounter('test'));
    }

    public function test_batch_observe(): void
    {
        $instrument = $this->createMock(AsynchronousInstrument::class);
        $this->assertInstanceOf(ObservableCallbackInterface::class, $this->meter->batchObserve(fn () => null, $instrument));
    }
}
