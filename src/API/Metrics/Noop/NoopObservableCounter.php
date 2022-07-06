<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\API\Metrics\ObservableCounterInterface;

/**
 * @internal
 */
final class NoopObservableCounter implements ObservableCounterInterface
{
    public function observe(callable $callback): ObservableCallbackInterface
    {
        return new NoopObservableCallback();
    }
}
