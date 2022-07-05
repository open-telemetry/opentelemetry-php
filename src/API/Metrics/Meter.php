<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

interface Meter
{

    /**
     * Creates a `Counter`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @return Counter created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#counter-creation
     */
    public function createCounter(
        string $name,
        ?string $unit = null,
        ?string $description = null
    ): Counter;

    /**
     * Creates an `ObservableCounter`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @param callable ...$callbacks responsible for reporting measurements
     * @return ObservableCounter created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#asynchronous-counter-creation
     */
    public function createObservableCounter(
        string $name,
        ?string $unit = null,
        ?string $description = null,
        callable ...$callbacks
    ): ObservableCounter;

    /**
     * Creates a `Histogram`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @return Histogram created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#histogram-creation
     */
    public function createHistogram(
        string $name,
        ?string $unit = null,
        ?string $description = null
    ): Histogram;

    /**
     * Creates an `ObservableGauge`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @param callable ...$callbacks responsible for reporting measurements
     * @return ObservableGauge created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#asynchronous-gauge-creation
     */
    public function createObservableGauge(
        string $name,
        ?string $unit = null,
        ?string $description = null,
        callable ...$callbacks
    ): ObservableGauge;

    /**
     * Creates an `UpDownCounter`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @return UpDownCounter created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#updowncounter-creation
     */
    public function createUpDownCounter(
        string $name,
        ?string $unit = null,
        ?string $description = null
    ): UpDownCounter;

    /**
     * Creates an `ObservableUpDownCounter`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @param callable ...$callbacks responsible for reporting measurements
     * @return ObservableUpDownCounter created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#asynchronous-updowncounter-creation
     */
    public function createObservableUpDownCounter(
        string $name,
        ?string $unit = null,
        ?string $description = null,
        callable ...$callbacks
    ): ObservableUpDownCounter;
}
