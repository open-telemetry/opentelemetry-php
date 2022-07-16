<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use ArrayAccess;
use Closure;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Metrics\MetricObserver\CallbackDestructor;

/**
 * @internal
 */
interface MetricObserverInterface
{
    /**
     * @param Closure(ObserverInterface):void $callback
     */
    public function observe(Closure $callback): int;

    public function has(int $token): bool;

    public function cancel(int $token): void;

    /**
     * @return ArrayAccess<object, CallbackDestructor>
     */
    public function destructors(): ArrayAccess;
}
