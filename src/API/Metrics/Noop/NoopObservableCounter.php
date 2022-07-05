<?php declare(strict_types=1);
namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\ObservableCallback;
use OpenTelemetry\API\Metrics\ObservableCounter;

/**
 * @internal
 */
final class NoopObservableCounter implements ObservableCounter {

    public function observe(callable $callback): ObservableCallback {
        return new NoopObservableCallback();
    }
}
