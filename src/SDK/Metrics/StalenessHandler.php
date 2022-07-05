<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics;

use Closure;

interface StalenessHandler {

    public function onStale(Closure $callback): void;
}
