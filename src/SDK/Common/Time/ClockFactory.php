<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

final class ClockFactory implements ClockFactoryInterface
{
    private static ?ClockInterface $default = null;

    public static function create(): self
    {
        return new self();
    }

    public function build(): ClockInterface
    {
        return new SystemClock();
    }

    public static function getDefault(): ClockInterface
    {
        return self::$default ?? self::$default = self::create()->build();
    }

    public static function setDefault(?ClockInterface $clock): void
    {
        self::$default = $clock;
    }
}
