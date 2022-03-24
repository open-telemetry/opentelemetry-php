<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

interface ClockInterface
{
    public const NANOS_PER_SECOND = 1_000_000_000;
    public const NANOS_PER_MILLISECOND = 1_000_000;
    public const NANOS_PER_MICROSECOND = 1_000;

    /**
     * Returns the current epoch wall-clock timestamp in nanoseconds.
     * This timestamp should _ONLY_ be used to compute a current time.
     * Use {@see \OpenTelemetry\SDK\Common\Time\AbstractClock::nanoTime} for calculating
     * durations.
     */
    public function now(): int;

    /**
     * Returns a high resolution timestamp that should _ONLY_ be used to calculate elapsed time.
     */
    public function nanoTime(): int;
}
