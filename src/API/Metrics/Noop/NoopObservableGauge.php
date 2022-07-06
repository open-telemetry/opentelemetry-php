<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\API\Metrics\ObservableGaugeInterface;

/**
 * @internal
 */
final class NoopObservableGauge implements ObservableGaugeInterface
{
    public function observe(callable $callback): ObservableCallbackInterface
    {
        return new NoopObservableCallback();
    }
}
