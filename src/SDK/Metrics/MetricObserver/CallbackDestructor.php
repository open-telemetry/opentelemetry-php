<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricObserver;

use OpenTelemetry\SDK\Metrics\MetricObserverInterface;
use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;

/**
 * @internal
 */
final class CallbackDestructor
{
    /**
     * @var array<int, int>
     */
    public array $tokens = [];
    private MetricObserverInterface $metricObserver;
    private ReferenceCounterInterface $referenceCounter;

    public function __construct(MetricObserverInterface $metricObserver, ReferenceCounterInterface $referenceCounter)
    {
        $this->metricObserver = $metricObserver;
        $this->referenceCounter = $referenceCounter;
    }

    public function __destruct()
    {
        foreach ($this->tokens as $token) {
            if ($this->metricObserver->has($token)) {
                $this->metricObserver->cancel($token);
                $this->referenceCounter->release();
            }
        }
    }
}
