<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Util;

final class TestClock implements \OpenTelemetry\API\ClockInterface
{
    public const DEFAULT_START_EPOCH = 1633060331386955008; // Fri Oct 01 2021 03:52:11 UTC

    private int $currentEpochNanos;

    public function __construct(int $currentEpochNanos = self::DEFAULT_START_EPOCH)
    {
        $this->currentEpochNanos = $currentEpochNanos;
    }

    public function advanceSeconds(int $seconds = 1): void
    {
        $this->advance($seconds * \OpenTelemetry\API\ClockInterface::NANOS_PER_SECOND);
    }

    public function advance(int $nanoSeconds = 1): void
    {
        $this->currentEpochNanos += $nanoSeconds;
    }

    public function setTime(int $nanoSeconds): void
    {
        $this->currentEpochNanos = $nanoSeconds;
    }

    public function now(): int
    {
        return $this->currentEpochNanos;
    }

    public function nanoTime(): int
    {
        return $this->currentEpochNanos;
    }
}
