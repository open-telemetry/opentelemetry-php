<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricObserver;

use Closure;
use OpenTelemetry\API\Metrics\Observer;
use OpenTelemetry\SDK\Metrics\MetricObserver;
use OpenTelemetry\SDK\Metrics\ReferenceCounter;
use function spl_object_id;

final class MultiObserver implements MetricObserver
{
    private ReferenceCounter $referenceCounter;
    private array $callbacks = [];

    public function __construct(ReferenceCounter $referenceCounter)
    {
        $this->referenceCounter = $referenceCounter;
    }

    public function __invoke(Observer $observer): void
    {
        foreach ($this->callbacks as $token => $callback) {
            if (isset($this->callbacks[$token])) {
                $callback($observer);
            }
        }
    }

    public function observe(Closure $callback): int
    {
        $token = spl_object_id($callback);
        if (isset($this->callbacks[$token])) {
            return -1;
        }

        $this->callbacks[$token] = $callback;
        $this->referenceCounter->acquire();

        return $token;
    }

    public function cancel(int $token): void
    {
        if (!isset($this->callbacks[$token])) {
            return;
        }

        unset($this->callbacks[$token]);
        $this->referenceCounter->release();
    }
}
