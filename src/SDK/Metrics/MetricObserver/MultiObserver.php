<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricObserver;

use function array_key_last;
use Closure;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Metrics\MetricObserverInterface;

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
        $this->callbacks[] = $callback;

        return array_key_last($this->callbacks);
    }

    public function has(int $token): bool
    {
        return isset($this->callbacks[$token]);
    }

    public function cancel(int $token): void
    {
        unset($this->callbacks[$token]);
    }
}
