<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\SDK\Metrics\MetricObserver\CallbackDestructor;

/**
 * @internal
 */
final class ObservableCallback implements ObservableCallbackInterface
{
    private MetricObserverInterface $metricObserver;
    private ReferenceCounterInterface $referenceCounter;
    private int $token;
    private ?CallbackDestructor $callbackDestructor;

    public function __construct(MetricObserverInterface $metricObserver, ReferenceCounterInterface $referenceCounter, int $token, ?CallbackDestructor $callbackDestructor)
    {
        $this->metricObserver = $metricObserver;
        $this->referenceCounter =  $referenceCounter;
        $this->token = $token;
        $this->callbackDestructor = $callbackDestructor;
    }

    public function detach(): void
    {
        if (!$this->metricObserver->has($this->token)) {
            return;
        }

        $this->metricObserver->cancel($this->token);
        $this->referenceCounter->release();
        if ($this->callbackDestructor !== null) {
            unset($this->callbackDestructor->tokens[$this->token]);
        }
    }

    public function __destruct()
    {
        if ($this->callbackDestructor !== null) {
            return;
        }
        if (!$this->metricObserver->has($this->token)) {
            return;
        }

        $this->referenceCounter->acquire(true);
        $this->referenceCounter->release();
    }
}
