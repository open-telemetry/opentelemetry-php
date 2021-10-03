<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function intdiv;
use OpenTelemetry\API\Trace as API;

abstract class AbstractClock implements API\ClockInterface
{
    private static ?API\ClockInterface $testClock;

    public static function getDefault(): API\ClockInterface
    {
        return self::$testClock ?? SystemClock::getInstance();
    }

    /**
     * @internal
     * @psalm-internal OpenTelemetry
     */
    public static function setTestClock(?API\ClockInterface $clock = null): void
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
