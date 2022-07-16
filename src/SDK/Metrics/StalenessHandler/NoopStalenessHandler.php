<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\StalenessHandler;

use Closure;
use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;

/**
 * @internal
 */
final class NoopStalenessHandler implements StalenessHandlerInterface, ReferenceCounterInterface
{
    public function acquire(bool $persistent = false): void
    {
        // no-op
    }

    public function release(): void
    {
        // no-op
    }

    public function onStale(Closure $callback): void
    {
        // no-op
    }
}
