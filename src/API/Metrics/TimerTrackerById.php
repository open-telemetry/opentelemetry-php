<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Common\Time\ClockInterface;

class TimerTrackerById
{
    /**
     * @var array <string|int, int|float>
     */
    protected array $timers = [];
    protected ClockInterface $clock;

    public function __construct(?ClockInterface $clock = null)
    {
        $this->clock = $clock ?? Clock::getDefault();
    }

    public function start(string|int $id): void
    {
        $this->timers[$id] = $this->clock->now();
    }

    public function durationNanos(string|int $id): int|float
    {
        if (!isset($this->timers[$id])) {
            return 0;
        }

        return ($this->clock->now() - $this->timers[$id]);
    }

    public function durationMs(string|int $id): float
    {
        return $this->durationNanos($id) / ClockInterface::NANOS_PER_MILLISECOND;
    }
}
