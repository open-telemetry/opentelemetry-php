<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\ObservableCallback;
use OpenTelemetry\API\Metrics\ObservableUpDownCounter;

/**
 * @internal
 */
final class NoopObservableUpDownCounter implements ObservableUpDownCounter
{
    public function observe(callable $callback): ObservableCallback
    {
        return new NoopObservableCallback();
    }
}
