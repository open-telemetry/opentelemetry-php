<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use Closure;
use OpenTelemetry\API\Metrics\Counter;
use OpenTelemetry\API\Metrics\Histogram;
use OpenTelemetry\API\Metrics\Meter;
use OpenTelemetry\API\Metrics\ObservableCounter;
use OpenTelemetry\API\Metrics\ObservableGauge;
use OpenTelemetry\API\Metrics\ObservableUpDownCounter;
use OpenTelemetry\API\Metrics\UpDownCounter;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\Time\ClockInterface;

final class SdkMeter implements Meter
{
    private MetricFactory $metricFactory;
    private ClockInterface $clock;
    private InstrumentationScopeInterface $instrumentationScope;

    public function __construct(
        MetricFactory $metricFactory,
        ClockInterface $clock,
        InstrumentationScopeInterface $instrumentationScope
    ) {
        $this->metricFactory = $metricFactory;
        $this->clock = $clock;
        $this->instrumentationScope = $instrumentationScope;
    }

    public function createCounter(string $name, ?string $unit = null, ?string $description = null): Counter
    {
        [$writer, $referenceCounter] = $this->metricFactory->createSynchronousWriter(
            $this->instrumentationScope,
            new Instrument(InstrumentType::COUNTER, $name, $unit, $description),
            $this->clock->now(),
        );

        return new SdkCounter($writer, $referenceCounter, $this->clock);
    }

    public function createObservableCounter(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableCounter
    {
        [$observer, $referenceCounter] = $this->metricFactory->createAsynchronousObserver(
            $this->instrumentationScope,
            new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, $name, $unit, $description),
            $this->clock->now(),
        );

        foreach ($callbacks as $callback) {
            $observer->observe(Closure::fromCallable($callback));
        }

        return new SdkObservableCounter($observer, $referenceCounter);
    }

    public function createHistogram(string $name, ?string $unit = null, ?string $description = null): Histogram
    {
        [$writer, $referenceCounter] = $this->metricFactory->createSynchronousWriter(
            $this->instrumentationScope,
            new Instrument(InstrumentType::HISTOGRAM, $name, $unit, $description),
            $this->clock->now(),
        );

        return new SdkHistogram($writer, $referenceCounter, $this->clock);
    }

    public function createObservableGauge(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableGauge
    {
        [$observer, $referenceCounter] = $this->metricFactory->createAsynchronousObserver(
            $this->instrumentationScope,
            new Instrument(InstrumentType::ASYNCHRONOUS_GAUGE, $name, $unit, $description),
            $this->clock->now(),
        );

        foreach ($callbacks as $callback) {
            $observer->observe(Closure::fromCallable($callback));
        }

        return new SdkObservableGauge($observer, $referenceCounter);
    }

    public function createUpDownCounter(string $name, ?string $unit = null, ?string $description = null): UpDownCounter
    {
        [$writer, $referenceCounter] = $this->metricFactory->createSynchronousWriter(
            $this->instrumentationScope,
            new Instrument(InstrumentType::UP_DOWN_COUNTER, $name, $unit, $description),
            $this->clock->now(),
        );

        return new SdkUpDownCounter($writer, $referenceCounter, $this->clock);
    }

    public function createObservableUpDownCounter(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableUpDownCounter
    {
        [$observer, $referenceCounter] = $this->metricFactory->createAsynchronousObserver(
            $this->instrumentationScope,
            new Instrument(InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER, $name, $unit, $description),
            $this->clock->now(),
        );

        foreach ($callbacks as $callback) {
            $observer->observe(Closure::fromCallable($callback));
        }

        return new SdkObservableUpDownCounter($observer, $referenceCounter);
    }
}
