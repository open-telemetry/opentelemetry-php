<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\CounterInterface;

/**
 * @internal
 */
final class NoopCounter implements CounterInterface
{
    #[\Override]
    public function add($amount, iterable $attributes = [], $context = null): void
    {
        // no-op
    }

    #[\Override]
    public function isEnabled(): bool
    {
        return false;
    }
}
