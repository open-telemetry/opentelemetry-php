<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

interface MeterInterface
{

    /**
     * Creates a `Counter`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @return CounterInterface created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#counter-creation
     */
    public function createCounter(
        string $name,
        ?string $unit = null,
        ?string $description = null
    ): CounterInterface;

    /**
     * Creates an `ObservableCounter`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @param callable ...$callbacks responsible for reporting measurements
     * @return ObservableCounterInterface created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#asynchronous-counter-creation
     */
    public function createObservableCounter(
        string $name,
        ?string $unit = null,
        ?string $description = null,
        callable ...$callbacks
    ): ObservableCounterInterface;

    /**
     * Creates a `Histogram`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @return HistogramInterface created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#histogram-creation
     */
    public function createHistogram(
        string $name,
        ?string $unit = null,
        ?string $description = null
    ): HistogramInterface;

    /**
     * Creates an `ObservableGauge`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @param callable ...$callbacks responsible for reporting measurements
     * @return ObservableGaugeInterface created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#asynchronous-gauge-creation
     */
    public function createObservableGauge(
        string $name,
        ?string $unit = null,
        ?string $description = null,
        callable ...$callbacks
    ): ObservableGaugeInterface;

    /**
     * Creates an `UpDownCounter`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @return UpDownCounterInterface created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#updowncounter-creation
     */
    public function createUpDownCounter(
        string $name,
        ?string $unit = null,
        ?string $description = null
    ): UpDownCounterInterface;

    /**
     * Creates an `ObservableUpDownCounter`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @param callable ...$callbacks responsible for reporting measurements
     * @return ObservableUpDownCounterInterface created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#asynchronous-updowncounter-creation
     */
    public function createObservableUpDownCounter(
        string $name,
        ?string $unit = null,
        ?string $description = null,
        callable ...$callbacks
    ): ObservableUpDownCounterInterface;
}
