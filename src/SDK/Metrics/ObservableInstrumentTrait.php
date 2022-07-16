<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use ArrayAccess;
use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\API\Metrics\ObserverInterface;
use function OpenTelemetry\SDK\Common\Util\closure;
use function OpenTelemetry\SDK\Common\Util\weaken;
use OpenTelemetry\SDK\Metrics\MetricObserver\CallbackDestructor;

/**
 * @internal
 */
trait ObservableInstrumentTrait
{
    private MetricObserverInterface $metricObserver;
    private ReferenceCounterInterface $referenceCounter;
    private ArrayAccess $callbackDestructors;

    public function __construct(
        MetricObserverInterface $metricObserver,
        ReferenceCounterInterface $referenceCounter
    ) {
        $this->metricObserver = $metricObserver;
        $this->referenceCounter = $referenceCounter;

        $this->referenceCounter->acquire();
    }

    public function __destruct()
    {
        $this->referenceCounter->release();
    }

    /**
     * @param callable(ObserverInterface): void $callback
     */
    public function observe(callable $callback, bool $weaken = false): ObservableCallbackInterface
    {
        $target = null;
        $callback = closure($callback);
        if ($weaken) {
            $callback = weaken($callback, $target);
        }

        /** @psalm-var \Closure(ObserverInterface): void $callback */
        $token = $this->metricObserver->observe($callback);
        $this->referenceCounter->acquire();

        $destructor = null;
        if ($object = $target) {
            $destructor = $this->metricObserver->destructors()[$object] ??= new CallbackDestructor($this->metricObserver, $this->referenceCounter);
            $destructor->tokens[$token] = $token;
        }

        return new ObservableCallback($this->metricObserver, $this->referenceCounter, $token, $destructor);
    }
}
