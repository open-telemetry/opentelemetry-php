<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

interface StopWatchFactoryInterface
{
    public static function create(?ClockInterface $clock = null, ?int $initialStartTime = null): self;

    public static function fromClockFactory(ClockFactoryInterface $factory, ?int $initialStartTime = null): self;

    public function build(): StopWatchInterface;

    public static function getDefault(): StopWatchInterface;

    public static function setDefault(?StopWatchInterface $default): void;
}
