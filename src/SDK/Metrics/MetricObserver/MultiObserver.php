<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricObserver;

use function array_key_last;
use ArrayAccess;
use Closure;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Common\Util\WeakMap;
use OpenTelemetry\SDK\Metrics\MetricObserverInterface;

/**
 * @internal
 */
final class MultiObserver implements MetricObserverInterface
{
    /**
     * @var array<int, Closure(ObserverInterface):void>
     */
    private array $callbacks = [];
    private ?ArrayAccess $weakMap = null;

    public function __invoke(ObserverInterface $observer): void
    {
        foreach ($this->callbacks as $token => $callback) {
            if (isset($this->callbacks[$token])) { //@phpstan-ignore-line callbacks can be unregistered during collection
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

    public function destructors(): ArrayAccess
    {
        return $this->weakMap ??= WeakMap::create();
    }
}
