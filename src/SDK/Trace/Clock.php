<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function intdiv;
use OpenTelemetry\API\Trace as API;

abstract class Clock implements API\Clock
{
    private static ?API\Clock $testClock;

    public static function getDefault(): API\Clock
    {
        return self::$testClock ?? SystemClock::getInstance();
    }

    /**
     * @internal
     * @psalm-internal OpenTelemetry
     */
    public static function setTestClock(?API\Clock $clock = null): void
    {
        self::$testClock = $clock;
    }

    /** @psalm-pure */
    public static function nanosToMicro(int $nanoseconds): int
    {
        return intdiv($nanoseconds, 1000);
    }

    /** @psalm-pure */
    public static function nanosToMilli(int $nanoseconds): int
    {
        return intdiv($nanoseconds, 1000000);
    }

    /** @psalm-pure */
    public static function secondsToNanos(int $seconds): int
    {
        return $seconds * self::NANOS_PER_SECOND;
    }
}
