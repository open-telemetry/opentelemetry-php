<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace\Test;

use OpenTelemetry\Trace as API;

final class TestClock implements API\Clock
{
    private int $currentEpochNanos;

    public function __construct(int $currentEpochNanos)
    {
        $this->currentEpochNanos = $currentEpochNanos;
    }

    public function advanceSeconds(int $seconds = 1): void
    {
        $this->advance($seconds * API\Clock::NANOS_PER_SECOND);
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
