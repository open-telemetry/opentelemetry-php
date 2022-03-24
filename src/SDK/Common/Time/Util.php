<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

class Util
{
    /** @psalm-pure */
    public static function nanosToMicro(int $nanoseconds): int
    {
        return intdiv($nanoseconds, ClockInterface::NANOS_PER_MICROSECOND);
    }

    /** @psalm-pure */
    public static function nanosToMilli(int $nanoseconds): int
    {
        return intdiv($nanoseconds, ClockInterface::NANOS_PER_MILLISECOND);
    }

    /** @psalm-pure */
    public static function secondsToNanos(int $seconds): int
    {
        return $seconds * ClockInterface::NANOS_PER_SECOND;
    }
}
