<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\StalenessHandler;

use function assert;
use Closure;
use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;

/**
 * @internal
 */
final class DelayedStalenessHandler implements StalenessHandlerInterface, ReferenceCounterInterface
{
    private Closure $stale;
    private Closure $freshen;

    /** @var Closure[]|null */
    private ?array $onStale = [];
    private int $count = 0;

    public function __construct(Closure $stale, Closure $freshen)
    {
        $this->stale = $stale;
        $this->freshen = $freshen;
    }

    public function acquire(bool $persistent = false): void
    {
        if ($this->count === 0) {
            ($this->freshen)($this);
        }

        $this->count++;

        if ($persistent) {
            $this->onStale = null;
        }
    }

    public function release(): void
    {
        if (--$this->count || $this->onStale === null) {
            return;
        }

        ($this->stale)($this);
    }

    public function onStale(Closure $callback): void
    {
        if ($this->onStale === null) {
            return;
        }

        $this->onStale[] = $callback;
    }

    public function triggerStale(): void
    {
        assert($this->onStale !== null);

        $callbacks = $this->onStale;
        $this->onStale = [];
        foreach ($callbacks as $callback) {
            $callback();
        }
    }
}
