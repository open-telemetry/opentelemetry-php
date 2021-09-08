<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

class Clock implements API\Clock
{
    private static $instance;

    /** @var API\RealtimeClock */
    public $realtime_clock;

    /** @var API\MonotonicClock */
    public $monotonic_clock;

    private function __construct()
    {
        $this->realtime_clock = new RealtimeClock();
        $this->monotonic_clock = new MonotonicClock();
    }

    public static function get(): Clock
    {
        if (!self::$instance) {
            self::$instance = new Clock();
        }

        return self::$instance;
    }

    /** @return array{int, int} */
    public function moment(): array
    {
        return [$this->realtime_clock->now(), $this->monotonic_clock->now()];
    }

    public function timestamp(): int
    {
        return $this->realtime_clock->now();
    }

    public function now(): int
    {
        return $this->monotonic_clock->now();
    }
}
