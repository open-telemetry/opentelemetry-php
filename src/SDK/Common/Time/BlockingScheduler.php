<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;

final class BlockingScheduler implements SchedulerInterface
{
    use LogsMessagesTrait;
    public function __construct()
    {
        self::logInfo('setting Blocking Delay scheduler');
    }

    public function delay(int $timeout): void
    {
        self::logInfo("Delaying the execution by $timeout milliseconds");
        usleep(abs($timeout) * 1000);
    }
}
