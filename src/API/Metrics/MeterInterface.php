<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

interface MeterInterface
{

    /**
     * Reports measurements for multiple asynchronous instrument from a single callback.
     *
     * The callback receives an {@link ObserverInterface} for each instrument. All provided
     * instruments have to be created by this meter.
     *
     * ```php
     * $callback = $meter->batchObserve(
     *     function(
     *         ObserverInterface $usageObserver,
     *         ObserverInterface $pressureObserver,
     *     ): void {
     *         [$usage, $pressure] = expensive_system_call();
     *         $usageObserver->observe($usage);
     *         $pressureObserver->observe($pressure);
     *     },
     *     $meter->createObservableCounter('usage', description: 'count of items used'),
     *     $meter->createObservableGauge('pressure', description: 'force per unit area'),
     * );
     * ```
     *
     * @param callable $callback function responsible for reporting the measurements
     * @param AsynchronousInstrument $instrument first instrument to report measurements for
     * @param AsynchronousInstrument ...$instruments additional instruments to report measurements for
     * @return ObservableCallbackInterface token to detach callback
     *
     * @see https://opentelemetry.io/docs/specs/otel/metrics/api/#multiple-instrument-callbacks
     */
    public function batchObserve(
        callable $callback,
        AsynchronousInstrument $instrument,
        AsynchronousInstrument ...$instruments
    ): ObservableCallbackInterface;

    /**
     * Creates a `Counter`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @param array $advisory an optional set of recommendations
     * @return CounterInterface created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#counter-creation
     */
    public function createCounter(
        string $name,
        ?string $unit = null,
        ?string $description = null,
        array $advisory = []
    ): CounterInterface;

    /**
     * Creates an `ObservableCounter`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @param array|callable $advisory an optional set of recommendations, or
     *        deprecated: the first callback to report measurements
     * @param callable ...$callbacks responsible for reporting measurements
     * @return ObservableCounterInterface created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#asynchronous-counter-creation
     */
    public function createObservableCounter(
        string $name,
        ?string $unit = null,
        ?string $description = null,
        $advisory = [],
        callable ...$callbacks
    ): ObservableCounterInterface;

    /**
     * Creates a `Histogram`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @param array $advisory an optional set of recommendations, e.g.
     *        <code>['ExplicitBucketBoundaries' => [0.25, 0.5, 1, 5]]</code>
     * @return HistogramInterface created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#histogram-creation
     */
    public function createHistogram(
        string $name,
        ?string $unit = null,
        ?string $description = null,
        array $advisory = []
    ): HistogramInterface;

    /**
     * Creates an `ObservableGauge`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @param array|callable $advisory an optional set of recommendations, or
     *        deprecated: the first callback to report measurements
     * @param callable ...$callbacks responsible for reporting measurements
     * @return ObservableGaugeInterface created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#asynchronous-gauge-creation
     */
    public function createObservableGauge(
        string $name,
        ?string $unit = null,
        ?string $description = null,
        $advisory = [],
        callable ...$callbacks
    ): ObservableGaugeInterface;

    /**
     * Creates an `UpDownCounter`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @param array $advisory an optional set of recommendations
     * @return UpDownCounterInterface created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#updowncounter-creation
     */
    public function createUpDownCounter(
        string $name,
        ?string $unit = null,
        ?string $description = null,
        array $advisory = []
    ): UpDownCounterInterface;

    /**
     * Creates an `ObservableUpDownCounter`.
     *
     * @param string $name name of the instrument
     * @param string|null $unit unit of measure
     * @param string|null $description description of the instrument
     * @param array|callable $advisory an optional set of recommendations, or
     *        deprecated: the first callback to report measurements
     * @param callable ...$callbacks responsible for reporting measurements
     * @return ObservableUpDownCounterInterface created instrument
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#asynchronous-updowncounter-creation
     */
    public function createObservableUpDownCounter(
        string $name,
        ?string $unit = null,
        ?string $description = null,
        $advisory = [],
        callable ...$callbacks
    ): ObservableUpDownCounterInterface;
}
