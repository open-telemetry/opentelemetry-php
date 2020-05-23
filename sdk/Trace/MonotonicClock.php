<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

//Please read the documentation on the MonotonicClock interface.
class MonotonicClock implements \OpenTelemetry\Trace\MonotonicClock
{
    public function now(): int
    {
        return hrtime(true);
    }
}
