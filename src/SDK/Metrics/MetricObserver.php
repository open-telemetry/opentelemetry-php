<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use Closure;
use OpenTelemetry\API\Metrics\Observer;

interface MetricObserver
{
    /**
     * @param Closure(Observer):void $callback
     */
    public function observe(Closure $callback): int;

    public function cancel(int $token): void;
}
