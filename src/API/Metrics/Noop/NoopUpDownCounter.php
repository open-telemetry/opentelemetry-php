<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\UpDownCounter;

/**
 * @internal
 */
final class NoopUpDownCounter implements UpDownCounter
{
    public function add($amount, iterable $attributes = [], $context = null): void
    {
        // no-op
    }
}
