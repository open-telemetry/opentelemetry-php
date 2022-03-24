<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

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
}
