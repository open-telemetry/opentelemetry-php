<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

use Closure;

/**
 * @psalm-suppress InvalidArgument
 */
class LateBindingMeter implements MeterInterface
{
    private ?MeterInterface $meter = null;

    /** @param Closure(): MeterInterface $factory */
    public function __construct(
        private readonly Closure $factory,
    ) {
    }

    public function batchObserve(callable $callback, AsynchronousInstrument $instrument, AsynchronousInstrument ...$instruments): ObservableCallbackInterface
    {
        return ($this->meter ??= ($this->factory)())->batchObserve($callback, $instrument, ...$instruments);
    }

    public function createCounter(string $name, ?string $unit = null, ?string $description = null, array $advisory = []): CounterInterface
    {
        return ($this->meter ??= ($this->factory)())->createCounter($name, $unit, $description, $advisory);
    }

    public function createObservableCounter(string $name, ?string $unit = null, ?string $description = null, callable|array $advisory = [], callable ...$callbacks): ObservableCounterInterface
    {
        return ($this->meter ??= ($this->factory)())->createObservableCounter($name, $unit, $description, $advisory, ...$callbacks);
    }

    public function createHistogram(string $name, ?string $unit = null, ?string $description = null, array $advisory = []): HistogramInterface
    {
        return ($this->meter ??= ($this->factory)())->createHistogram($name, $unit, $description, $advisory);
    }

    public function createGauge(string $name, ?string $unit = null, ?string $description = null, array $advisory = []): GaugeInterface
    {
        return ($this->meter ??= ($this->factory)())->createGauge($name, $unit, $description, $advisory);
    }

    public function createObservableGauge(string $name, ?string $unit = null, ?string $description = null, callable|array $advisory = [], callable ...$callbacks): ObservableGaugeInterface
    {
        return ($this->meter ??= ($this->factory)())->createObservableGauge($name, $unit, $description, $advisory, ...$callbacks);
    }

    public function createUpDownCounter(string $name, ?string $unit = null, ?string $description = null, array $advisory = []): UpDownCounterInterface
    {
        return ($this->meter ??= ($this->factory)())->createUpDownCounter($name, $unit, $description, $advisory);
    }

    public function createObservableUpDownCounter(string $name, ?string $unit = null, ?string $description = null, callable|array $advisory = [], callable ...$callbacks): ObservableUpDownCounterInterface
    {
        return ($this->meter ??= ($this->factory)())->createObservableUpDownCounter($name, $unit, $description, $advisory, ...$callbacks);
    }
}
