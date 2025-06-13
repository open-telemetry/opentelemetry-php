<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Util;

class TimerTrackerByObject
{
    protected \WeakMap $timers;

    public function __construct()
    {
        $this->timers = new \WeakMap();
    }

    public function start(object $id): void
    {
        $this->timers[$id] = microtime(true);
    }

    public function durationMs(object $id): float
    {
        if ($this->timers->offsetExists($id) === false) {
            return 0;
        }

        return (microtime(true) - $this->timers[$id]) * 1000;
    }
}
