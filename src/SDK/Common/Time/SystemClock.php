<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

use function hrtime;
use function microtime;

final class SystemClock implements ClockInterface
{
    private static ?self $instance = null;
    private static int $referenceTime = 0;

    public function __construct()
    {
        self::init();
    }

    /**
     * @deprecated
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
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

    /**
     * @deprecated
     */
    public function nanoTime(): int
    {
        return $this->now();
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
