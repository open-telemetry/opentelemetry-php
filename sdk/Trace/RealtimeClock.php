<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

// Please read the documentation on the RealtimeClock interface.
class RealtimeClock implements \OpenTelemetry\Trace\RealtimeClock
{
    private const NSEC_PER_SEC = 1000000000;
    public function now(): int
    {
        return (int) (\microtime(true) * self::NSEC_PER_SEC);
    }
}
