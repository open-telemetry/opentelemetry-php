<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

/**
 * The API Specification says:
 * OpenTelemetry can operate on time values up to nanosecond (ns) precision.
 * The representation of those values is language specific.
 * A duration is the elapsed time between two events.
 *   - The minimal precision is milliseconds.
 *   - The maximal precision is nanoseconds.
 *
 * In the PHP standard library, the best suited function for measuring elapsed
 * time is `hrtime`, available since PHP 7.3. In other words, callers can
 * reasonably expect to have nanosecond resolution (nsec) on PHP 7.3 and newer.
 */
interface MonotonicClock
{
    /**
     * Represents the amount of time in nanoseconds since an unspecified point
     * in the past (for example, system start-up time, or the Epoch). This point
     * does not change after system start-up time.
     * @return int
     */
    public function now(): int;
}
