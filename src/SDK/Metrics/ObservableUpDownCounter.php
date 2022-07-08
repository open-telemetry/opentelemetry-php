<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use Closure;
use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\API\Metrics\ObservableUpDownCounterInterface;

final class ObservableUpDownCounter implements ObservableUpDownCounterInterface
{
    private MetricObserverInterface $metricObserver;
    private ReferenceCounterInterface $referenceCounter;

    public function __construct(MetricObserverInterface $metricObserver, ReferenceCounterInterface $referenceCounter)
    {
        $this->metricObserver = $metricObserver;
        $this->referenceCounter = $referenceCounter;

        $this->referenceCounter->acquire();
    }

    public function __destruct()
    {
        $this->referenceCounter->release();
    }

    public function observe(callable $callback): ObservableCallbackInterface
    {
        $token = $this->metricObserver->observe(Closure::fromCallable($callback));

        return new ObservableCallback($this->metricObserver, $this->referenceCounter, $token);
    }
}
