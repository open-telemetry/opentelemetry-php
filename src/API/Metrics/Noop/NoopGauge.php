<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\GaugeInterface;

/**
 * @internal
 */
final class NoopGauge implements GaugeInterface
{
    #[\Override]
    public function record(float|int $amount, iterable $attributes = [], $context = null): void
    {
        // no-op
    }

    #[\Override]
    public function isEnabled(): bool
    {
        return false;
    }
}
