<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\HistogramInterface;

/**
 * @internal
 */
final class NoopHistogram implements HistogramInterface
{
    public function record($amount, iterable $attributes = [], $context = null): void
    {
        // no-op
    }

    public function isEnabled(): bool
    {
        return false;
    }
}
