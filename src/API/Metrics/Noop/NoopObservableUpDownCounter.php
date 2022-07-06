<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\API\Metrics\ObservableUpDownCounterInterface;

/**
 * @internal
 */
final class NoopObservableUpDownCounter implements ObservableUpDownCounterInterface
{
    public function observe(callable $callback): ObservableCallbackInterface
    {
        return new NoopObservableCallback();
    }
}
