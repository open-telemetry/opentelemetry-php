<?php declare(strict_types=1);
namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\Counter;
use OpenTelemetry\API\Metrics\Histogram;
use OpenTelemetry\API\Metrics\Meter;
use OpenTelemetry\API\Metrics\ObservableCounter;
use OpenTelemetry\API\Metrics\ObservableGauge;
use OpenTelemetry\API\Metrics\ObservableUpDownCounter;
use OpenTelemetry\API\Metrics\UpDownCounter;

/**
 * @internal
 */
final class NoopMeter implements Meter {

    public function createCounter(string $name, ?string $unit = null, ?string $description = null): Counter {
        return new NoopCounter();
    }

    public function createObservableCounter(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableCounter {
        return new NoopObservableCounter();
    }

    public function createHistogram(string $name, ?string $unit = null, ?string $description = null): Histogram {
        return new NoopHistogram();
    }

    public function createObservableGauge(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableGauge {
        return new NoopObservableGauge();
    }

    public function createUpDownCounter(string $name, ?string $unit = null, ?string $description = null): UpDownCounter {
        return new NoopUpDownCounter();
    }

    public function createObservableUpDownCounter(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableUpDownCounter {
        return new NoopObservableUpDownCounter();
    }
}
