<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

class Util
{
    /** @psalm-pure */
    public static function nanosToMicros(int $nanoseconds): int
    {
        return intdiv($nanoseconds, ClockInterface::NANOS_PER_MICROSECOND);
    }

    /** @psalm-pure */
    public static function nanosToMillis(int $nanoseconds): int
    {
        return intdiv($nanoseconds, ClockInterface::NANOS_PER_MILLISECOND);
    }

    /** @psalm-pure */
    public static function secondsToNanos(int $seconds): int
    {
        return $seconds * ClockInterface::NANOS_PER_SECOND;
    }

    /** @psalm-pure */
    public static function millisToNanos(int $milliSeconds): int
    {
        return $milliSeconds * ClockInterface::NANOS_PER_MILLISECOND;
    }
}
