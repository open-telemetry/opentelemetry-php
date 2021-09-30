<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Clock
{
    public const NANOS_PER_SECOND = 1000000000;

    /**
     * Returns the current epoch wall-clock timestamp in nanoseconds.
     * This timestamp should _ONLY_ be used to compute a current time.
     * Use {@see Clock::nanoTime} for calculating durations.
     */
    public function now(): int;

    /**
     * Returns the current epoch monotonic timestamp in nanoseconds that can only be used to calculate elapsed time.
     */
    public function nanoTime(): int;
}
