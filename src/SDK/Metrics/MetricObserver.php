<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics;

use Closure;

interface MetricObserver {

    public function observe(Closure $callback): int;

    public function cancel(int $token): void;
}
