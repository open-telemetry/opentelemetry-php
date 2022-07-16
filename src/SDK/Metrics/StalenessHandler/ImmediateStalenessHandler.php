<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\StalenessHandler;

use Closure;
use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;

/**
 * @internal
 */
final class ImmediateStalenessHandler implements StalenessHandlerInterface, ReferenceCounterInterface
{
    /** @var Closure[]|null */
    private ?array $onStale = [];
    private int $count = 0;

    public function acquire(bool $persistent = false): void
    {
        $this->count++;

        if ($persistent) {
            $this->onStale = null;
        }
    }

    public function release(): void
    {
        if (--$this->count !== 0 || !$this->onStale) {
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
        if ($this->onStale === null) {
            return;
        }

        $this->onStale[] = $callback;
    }
}
