<?php
declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

class Clock implements API\Clock
{
    public function moment(): array
    {
        $realtime_clock = new RealtimeClock();
        $monotonic_clock = new MonotonicClock();
        return [$monotonic_clock->now(), $realtime_clock->now()];;
    }
}