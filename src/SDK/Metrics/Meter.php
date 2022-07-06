<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use Closure;
use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\ObservableCounterInterface;
use OpenTelemetry\API\Metrics\ObservableGaugeInterface;
use OpenTelemetry\API\Metrics\ObservableUpDownCounterInterface;
use OpenTelemetry\API\Metrics\UpDownCounterInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\Time\ClockInterface;

final class Meter implements MeterInterface
{
    private MetricFactoryInterface $metricFactory;
    private ClockInterface $clock;
    private InstrumentationScopeInterface $instrumentationScope;

    public function __construct(
        MetricFactoryInterface $metricFactory,
        ClockInterface $clock,
        InstrumentationScopeInterface $instrumentationScope
    ) {
        $this->metricFactory = $metricFactory;
        $this->clock = $clock;
        $this->instrumentationScope = $instrumentationScope;
    }

    public function createCounter(string $name, ?string $unit = null, ?string $description = null): CounterInterface
    {
        [$writer, $referenceCounter] = $this->metricFactory->createSynchronousWriter(
            $this->instrumentationScope,
            new Instrument(InstrumentType::COUNTER, $name, $unit, $description),
            $this->clock->now(),
        );

        return new Counter($writer, $referenceCounter, $this->clock);
    }

    public function createObservableCounter(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableCounterInterface
    {
        [$observer, $referenceCounter] = $this->metricFactory->createAsynchronousObserver(
            $this->instrumentationScope,
            new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, $name, $unit, $description),
            $this->clock->now(),
        );

        foreach ($callbacks as $callback) {
            $observer->observe(Closure::fromCallable($callback));
        }

        return new ObservableCounter($observer, $referenceCounter);
    }

    public function createHistogram(string $name, ?string $unit = null, ?string $description = null): HistogramInterface
    {
        [$writer, $referenceCounter] = $this->metricFactory->createSynchronousWriter(
            $this->instrumentationScope,
            new Instrument(InstrumentType::HISTOGRAM, $name, $unit, $description),
            $this->clock->now(),
        );

        return new Histogram($writer, $referenceCounter, $this->clock);
    }

    public function createObservableGauge(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableGaugeInterface
    {
        [$observer, $referenceCounter] = $this->metricFactory->createAsynchronousObserver(
            $this->instrumentationScope,
            new Instrument(InstrumentType::ASYNCHRONOUS_GAUGE, $name, $unit, $description),
            $this->clock->now(),
        );

        foreach ($callbacks as $callback) {
            $observer->observe(Closure::fromCallable($callback));
        }

        return new ObservableGauge($observer, $referenceCounter);
    }

    public function createUpDownCounter(string $name, ?string $unit = null, ?string $description = null): UpDownCounterInterface
    {
        [$writer, $referenceCounter] = $this->metricFactory->createSynchronousWriter(
            $this->instrumentationScope,
            new Instrument(InstrumentType::UP_DOWN_COUNTER, $name, $unit, $description),
            $this->clock->now(),
        );

        return new UpDownCounter($writer, $referenceCounter, $this->clock);
    }

    public function createObservableUpDownCounter(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableUpDownCounterInterface
    {
        [$observer, $referenceCounter] = $this->metricFactory->createAsynchronousObserver(
            $this->instrumentationScope,
            new Instrument(InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER, $name, $unit, $description),
            $this->clock->now(),
        );

        foreach ($callbacks as $callback) {
            $observer->observe(Closure::fromCallable($callback));
        }

        return new ObservableUpDownCounter($observer, $referenceCounter);
    }
}
