<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

/**
 * The API specification says:
 * OpenTelemetry can operate on time values up to nanosecond (ns) precision.
 * The representation of those values is language specific.
 * A timestamp is the time elapsed since the Unix epoch.
 *   - The minimal precision is milliseconds.
 *   - The maximal precision is nanoseconds.
 *
 * In the PHP standard library, the best suited function for time since the
 * Unix epoch is `microtime`, which uses microseconds or usecs. This interface
 * uses nanosecond resolution so it can represent nanoseconds as per
 * OpenTelemetry specification, but keep this mind as most implementations will
 * probably use `microtime` so don't expect nanosecond accuracy for a
 * RealtimeClock.
 */
interface RealtimeClock
{
    /**
     * @return int Number of nanoseconds since the Unix epoch
     */
    public function now(): int;
}
