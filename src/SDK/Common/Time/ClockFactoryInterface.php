<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

interface ClockFactoryInterface
{
    public static function create(): self;

    public function build(): ClockInterface;

    public static function getDefault(): ClockInterface;

    public static function setDefault(?ClockInterface $clock): void;
}
