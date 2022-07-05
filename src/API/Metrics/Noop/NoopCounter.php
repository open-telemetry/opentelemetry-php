<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\Counter;
use OpenTelemetry\Context\Context;

/**
 * @internal
 */
final class NoopCounter implements Counter
{
    public function add(float|int $amount, iterable $attributes = [], Context|false|null $context = null): void
    {
        // no-op
    }
}
