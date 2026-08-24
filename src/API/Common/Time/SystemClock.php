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
    private readonly int $referenceTime;

    public function __construct()
    {
        $this->referenceTime = self::calculateReferenceTime(
            microtime(true),
            hrtime(true)
        );
    }

    public static function create(): self
    {
        return new self();
    }

    /** @inheritDoc */
    #[\Override]
    public function now(): int
    {
        return $this->referenceTime + hrtime(true);
    }

    /**
     * Calculates the reference time which is later used to calculate the current wall clock time in nanoseconds by adding the current uptime.
     */
    private static function calculateReferenceTime(float $wallClockMicroTime, int $upTime): int
    {
        return ((int) ($wallClockMicroTime * (float) ClockInterface::NANOS_PER_SECOND)) - $upTime;
    }
}
