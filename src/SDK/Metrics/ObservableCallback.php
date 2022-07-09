<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\ObservableCallbackInterface;

final class ObservableCallback implements ObservableCallbackInterface
{
    private MetricObserverInterface $metricObserver;
    private ReferenceCounterInterface $referenceCounter;
    private ?int $token;

    public function __construct(MetricObserverInterface $metricObserver, ReferenceCounterInterface $referenceCounter, int $token)
    {
        $this->metricObserver = $metricObserver;
        $this->referenceCounter =  $referenceCounter;
        $this->token = $token;

        $this->referenceCounter->acquire();
    }

    public function detach(): void
    {
        if ($this->token === null) {
            return;
        }

        $this->metricObserver->cancel($this->token);
        $this->referenceCounter->release();
        $this->token = null;
    }

    public function __destruct()
    {
        if ($this->token === null) {
            return;
        }

        $this->referenceCounter->acquire(true);
        $this->referenceCounter->release();
    }
}
