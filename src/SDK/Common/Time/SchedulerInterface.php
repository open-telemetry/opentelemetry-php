<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

interface SchedulerInterface
{
    public function delay(int $timeout): void;
}
