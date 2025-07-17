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
    #[\Override]
    public function observe(callable $callback, bool $weaken = false): ObservableCallbackInterface
    {
        return new NoopObservableCallback();
    }

    #[\Override]
    public function isEnabled(): bool
    {
        return false;
    }
}
