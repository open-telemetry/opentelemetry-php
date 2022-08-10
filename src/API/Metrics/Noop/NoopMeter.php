<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\ObservableCounterInterface;
use OpenTelemetry\API\Metrics\ObservableGaugeInterface;
use OpenTelemetry\API\Metrics\ObservableUpDownCounterInterface;
use OpenTelemetry\API\Metrics\UpDownCounterInterface;

final class NoopMeter implements MeterInterface
{
    public function createCounter(string $name, ?string $unit = null, ?string $description = null): CounterInterface
    {
        return new NoopCounter();
    }

    public function createObservableCounter(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableCounterInterface
    {
        return new NoopObservableCounter();
    }

    public function createHistogram(string $name, ?string $unit = null, ?string $description = null): HistogramInterface
    {
        return new NoopHistogram();
    }

    public function createObservableGauge(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableGaugeInterface
    {
        return new NoopObservableGauge();
    }

    public function createUpDownCounter(string $name, ?string $unit = null, ?string $description = null): UpDownCounterInterface
    {
        return new NoopUpDownCounter();
    }

    public function createObservableUpDownCounter(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableUpDownCounterInterface
    {
        return new NoopObservableUpDownCounter();
    }
}
