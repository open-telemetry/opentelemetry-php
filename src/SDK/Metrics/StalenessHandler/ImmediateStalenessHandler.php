<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\StalenessHandler;

use Closure;
use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;

final class ImmediateStalenessHandler implements StalenessHandlerInterface, ReferenceCounterInterface
{

    /** @var Closure[] */
    private array $onStale = [];
    private int $count = 0;

    public function acquire(): void
    {
        $this->count++;
    }

    public function release(): void
    {
        if (--$this->count !== 0) {
            return;
        }

        $callbacks = $this->onStale;
        $this->onStale = [];
        foreach ($callbacks as $callback) {
            $callback();
        }
    }

    public function onStale(Closure $callback): void
    {
        $this->onStale[] = $callback;
    }
}
