<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Util;

class TimerTrackerById
{
    protected array $timers = [];

    public function start(string|int $id): void
    {
        $this->timers[$id] = microtime(true);
    }

    public function durationMs(string|int $id): float
    {
        if (!isset($this->timers[$id])) {
            return 0;
        }

        return (microtime(true) - $this->timers[$id]) * 1000;
    }
}
