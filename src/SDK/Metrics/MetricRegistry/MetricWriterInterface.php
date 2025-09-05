<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricRegistry;

use Closure;
use OpenTelemetry\SDK\Metrics\Instrument;

/**
 * @internal
 */
interface MetricWriterInterface
{
    public function record(Instrument $instrument, mixed $value, iterable $attributes = [], mixed $context = null): void;

    public function registerCallback(Closure $callback, Instrument $instrument, Instrument ...$instruments): int;

    public function unregisterCallback(int $callbackId): void;
    public function enabled(Instrument $instrument): bool;
}
