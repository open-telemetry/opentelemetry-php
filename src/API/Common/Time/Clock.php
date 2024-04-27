<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Time;

final class Clock
{
    private static ?ClockInterface $clock = null;

    public static function getDefault(): ClockInterface
    {
        return self::$clock ??= new SystemClock();
    }

    public static function setDefault(ClockInterface $clock): void
    {
        self::$clock = $clock;
    }

    public static function reset(): void
    {
        self::$clock = null;
    }
}
