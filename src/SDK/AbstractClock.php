<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use function intdiv;
use OpenTelemetry\API\ClockInterface;

abstract class AbstractClock implements ClockInterface
{
    private static ?ClockInterface $testClock;

    public static function getDefault(): ClockInterface
    {
        return self::$testClock ?? SystemClock::getInstance();
    }

    /**
     * @internal
     * @psalm-internal OpenTelemetry
     */
    public static function setTestClock(?ClockInterface $clock = null): void
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
