<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\UpDownCounterInterface;

/**
 * @internal
 */
final class NoopUpDownCounter implements UpDownCounterInterface
{
    public function add($amount, iterable $attributes = [], $context = null): void
    {
        // no-op
    }

    public function isEnabled(): bool
    {
        return false;
    }
}
