<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Time;

use function hrtime;
use function microtime;

/**
 * @internal OpenTelemetry
 */
final class SystemClock implements ClockInterface
{
    private static int $referenceTime = 0;

    public function __construct()
    {
        self::init();
    }

    public static function create(): self
    {
        return new self();
    }

    /** @inheritDoc */
    public function now(): int
    {
        return self::$referenceTime + hrtime(true);
    }

    private static function init(): void
    {
        if (self::$referenceTime > 0) {
            return;
        }

        self::$referenceTime = self::calculateReferenceTime(
            microtime(true),
            hrtime(true)
        );
    }

    /**
     * Calculates the reference time which is later used to calculate the current wall clock time in nanoseconds by adding the current uptime.
     */
    private static function calculateReferenceTime(float $wallClockMicroTime, int $upTime): int
    {
        return ((int) ($wallClockMicroTime * ClockInterface::NANOS_PER_SECOND)) - $upTime;
    }
}
