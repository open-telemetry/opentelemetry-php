<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

interface StopWatchInterface
{
    public function isRunning(): bool;

    public function start(): void;

    public function stop(): void;

    public function reset(): void;

    public function getElapsedTime(): int;

    public function getLastElapsedTime(): int;
}
