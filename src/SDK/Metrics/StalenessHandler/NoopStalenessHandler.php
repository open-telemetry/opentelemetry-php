<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\StalenessHandler;

use Closure;
use OpenTelemetry\SDK\Metrics\ReferenceCounter;
use OpenTelemetry\SDK\Metrics\StalenessHandler;

final class NoopStalenessHandler implements StalenessHandler, ReferenceCounter
{
    public function acquire(): void
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
