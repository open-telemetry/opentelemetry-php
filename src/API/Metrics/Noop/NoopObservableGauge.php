<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\ObservableCallback;
use OpenTelemetry\API\Metrics\ObservableGauge;

/**
 * @internal
 */
final class NoopObservableGauge implements ObservableGauge
{
    public function observe(callable $callback): ObservableCallback
    {
        return new NoopObservableCallback();
    }
}
