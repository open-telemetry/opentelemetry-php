<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\MetricRegistry;

use Closure;

interface MetricWriterInterface {

    public function record(int $instrumentId, $value, iterable $attributes = [], $context = null): void;

    public function registerCallback(Closure $callback, int ...$instrumentIds): int;

    public function unregisterCallback(int $callbackId): void;
}
