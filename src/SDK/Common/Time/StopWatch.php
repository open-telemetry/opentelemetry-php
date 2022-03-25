<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

final class StopWatch implements StopWatchInterface
{
    private const INITIAL_ELAPSED_TIME = 0;

    private ClockInterface $clock;
    private bool $running = false;
    private ?int $initialStartTime;
    private ?int $startTime = null;
    private ?int $stopTime = null;

    public function __construct(ClockInterface $clock, ?int $initialStartTime = null)
    {
        $this->clock = $clock;
        $this->initialStartTime = $initialStartTime;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    public function start(): void
    {
        // resolve start time as early as possible
        $startTime = $this->time();

        if ($this->isRunning()) {
            return;
        }

        $this->startTime = $startTime;
        if (!$this->hasBeenStarted()) {
            $this->initialStartTime = $startTime;
        }
        $this->running = true;
    }

    public function stop(): void
    {
        if (!$this->isRunning()) {
            return;
        }

        $this->stopTime = $this->time();
        $this->running = false;
    }

    public function reset(): void
    {
        $this->startTime = $this->initialStartTime = $this->isRunning() ? $this->time() : null;
    }

    public function getElapsedTime(): int
    {
        if (!$this->hasBeenStarted()) {
            return self::INITIAL_ELAPSED_TIME;
        }

        return $this->calculateElapsedTime();
    }

    public function getLastElapsedTime(): int
    {
        if (!$this->hasBeenStarted()) {
            return self::INITIAL_ELAPSED_TIME;
        }

        return $this->calculateLastElapsedTime();
    }

    private function time(): int
    {
        return $this->clock->now();
    }

    private function hasBeenStarted(): bool
    {
        return $this->initialStartTime !== null;
    }

    private function calculateElapsedTime(): int
    {
        $referenceTime = $this->isRunning()
            ? $this->time()
            : $this->getStopTime();

        return $referenceTime - $this->getInitialStartTime();
    }

    private function calculateLastElapsedTime(): int
    {
        $referenceTime = $this->isRunning()
            ? $this->time()
            : $this->getStopTime();

        return $referenceTime - $this->getStartTime();
    }

    private function getInitialStartTime(): ?int
    {
        return $this->initialStartTime;
    }

    private function getStartTime(): ?int
    {
        return $this->startTime;
    }

    private function getStopTime(): ?int
    {
        return $this->stopTime;
    }
}
