<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

final class StopWatchFactory implements StopWatchFactoryInterface
{
    private static ?StopWatchInterface $default = null;

    private ClockInterface $clock;
    private ?int $initialStartTime;

    public function __construct(?ClockInterface $clock = null, ?int $initialStartTime = null)
    {
        $this->clock = $clock ?? ClockFactory::getDefault();
        $this->initialStartTime = $initialStartTime;
    }

    public static function create(?ClockInterface $clock = null, ?int $initialStartTime = null): self
    {
        return new self($clock, $initialStartTime);
    }

    public static function fromClockFactory(ClockFactoryInterface $factory, ?int $initialStartTime = null): self
    {
        return self::create($factory->build(), $initialStartTime);
    }

    public function build(): StopWatch
    {
        return new StopWatch($this->clock, $this->initialStartTime);
    }

    public static function getDefault(): StopWatchInterface
    {
        return self::$default ?? self::$default = self::create()->build();
    }

    public static function setDefault(?StopWatchInterface $default): void
    {
        self::$default = $default;
    }
}
