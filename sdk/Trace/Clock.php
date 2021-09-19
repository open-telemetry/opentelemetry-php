<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use function intdiv;
use OpenTelemetry\Trace as API;

class Clock implements API\Clock
{
    private static ?self $instance = null;

    public static function get(): Clock
    {
        if (null === self::$instance) {
            self::$instance = new Clock();
        }

        return self::$instance;
    }

    /** @psalm-pure */
    public static function nanosToMicro(int $nanoseconds): int
    {
        return intdiv($nanoseconds, 1000);
    }

    /** @psalm-pure */
    public static function nanosToMilli(int $nanoseconds): int
    {
        return intdiv($nanoseconds, 1000000);
    }

    /** @var API\RealtimeClock */
    public $realtime_clock;

    /** @var API\MonotonicClock */
    public $monotonic_clock;

    private function __construct()
    {
        $this->realtime_clock = new RealtimeClock();
        $this->monotonic_clock = new MonotonicClock();
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
