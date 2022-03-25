<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

final class StopWatchFactory implements StopWatchFactoryInterface
{
    private static ?StopWatchInterface $default;

    private ?ClockInterface $clock;

    public function __construct(?ClockInterface $clock = null)
    {
        $this->clock = $clock ?? ClockFactory::getDefault();
    }

    public static function create(?ClockInterface $clock = null): self
    {
        return new self($clock);
    }

    public static function createFromClockFactory(ClockFactoryInterface $factory): self
    {
        return self::create($factory->build());
    }

    public function build(): StopWatch
    {
        return new StopWatch($this->clock);
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
