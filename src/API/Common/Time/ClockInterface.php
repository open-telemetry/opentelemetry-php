<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Time;

interface ClockInterface
{
    public const NANOS_PER_SECOND = 1_000_000_000;
    public const NANOS_PER_MILLISECOND = 1_000_000;
    public const NANOS_PER_MICROSECOND = 1_000;
    public const MICROS_PER_MILLISECOND = 1_000;
    public const MILLIS_PER_SECOND = 1_000;

    /**
     * Returns the current epoch wall-clock timestamp in nanoseconds
     */
    public function now(): int;
}
