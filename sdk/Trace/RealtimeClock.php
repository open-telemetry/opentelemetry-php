<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

// Please read the documentation on the RealtimeClock interface.
class RealtimeClock implements \OpenTelemetry\Trace\RealtimeClock
{
    private const NSEC_PER_SEC = 1000000000;
    private const NSEC_PER_USEC = 1000;
    public function now(): int
    {
        \sscanf(\microtime(), '%d %d', $usec, $seconds);

        return $seconds * self::NSEC_PER_SEC + $usec * self::NSEC_PER_USEC;
    }
}
