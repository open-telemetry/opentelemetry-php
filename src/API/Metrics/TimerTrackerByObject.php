<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Common\Time\ClockInterface;
use WeakMap;

class TimerTrackerByObject
{
    /**
     * @var WeakMap <object, int|float>
     */
    protected WeakMap $timers;
    protected ClockInterface $clock;

    public function __construct(?ClockInterface $clock = null)
    {
        $this->timers = new WeakMap();
        $this->clock = $clock ?? Clock::getDefault();
    }

    public function start(object $id): void
    {
        $this->timers[$id] = $this->clock->now();
    }

    public function durationNanos(object $id): int|float
    {
        if ($this->timers->offsetExists($id) === false) {
            return 0;
        }

        return $this->clock->now() - $this->timers[$id];
    }

    public function durationMs(object $id): float
    {
        return $this->durationNanos($id) / ClockInterface::NANOS_PER_MILLISECOND;
    }

    public function durationS(object $id): float
    {
        return $this->durationNanos($id) / ClockInterface::NANOS_PER_SECOND;
    }
}
