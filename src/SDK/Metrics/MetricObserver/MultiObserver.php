<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricObserver;

use Closure;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Metrics\MetricObserverInterface;
use function spl_object_id;

final class MultiObserver implements MetricObserverInterface
{
    /**
     * @var array<int, Closure(ObserverInterface):void>
     */
    private array $callbacks = [];

    public function __invoke(ObserverInterface $observer): void
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

        return $token;
    }

    public function cancel(int $token): void
    {
        if (!isset($this->callbacks[$token])) {
            return;
        }

        unset($this->callbacks[$token]);
    }
}
