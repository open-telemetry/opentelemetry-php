<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\StalenessHandler;

use ArrayAccess;
use Closure;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Common\Util\WeakMap;
use OpenTelemetry\SDK\Metrics\StalenessHandlerFactoryInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;
use Traversable;

final class DelayedStalenessHandlerFactory implements StalenessHandlerFactoryInterface
{
    private ClockInterface $clock;
    private int $nanoDelay;

    private Closure $stale;
    private Closure $freshen;

    /** @var ArrayAccess<DelayedStalenessHandler, int>&Traversable<DelayedStalenessHandler, int> */
    private $staleHandlers;

    /**
     * @param float $delay delay in seconds
     */
    public function __construct(ClockInterface $clock, float $delay)
    {
        $this->clock = $clock;
        $this->nanoDelay = (int) ($delay * 1e9);

        $this->stale = function (DelayedStalenessHandler $handler): void {
            $this->staleHandlers[$handler] = $this->clock->now();
        };
        $this->freshen = function (DelayedStalenessHandler $handler): void {
            unset($this->staleHandlers[$handler]);
        };

        $this->staleHandlers = WeakMap::create();
    }

    public function create(): StalenessHandlerInterface
    {
        $this->triggerStaleHandlers();

        return new DelayedStalenessHandler($this->stale, $this->freshen);
    }

    private function triggerStaleHandlers(): void
    {
        $expired = $this->clock->now() - $this->nanoDelay;
        foreach ($this->staleHandlers as $handler => $timestamp) {
            if ($timestamp > $expired) {
                break;
            }

            /** @var DelayedStalenessHandler $handler */
            unset($this->staleHandlers[$handler]);
            $handler->triggerStale();
        }
    }
}
