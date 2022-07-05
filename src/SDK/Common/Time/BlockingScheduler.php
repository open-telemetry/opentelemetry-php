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
    /**
     * delays the current thread by $timeout seconds
     *
     * @param int $timeout - no of mili seconds to delay the current execution
     * @return void
     */
    public function delay(int $timeout): void
    {
        self::logInfo("Delaying the execution by $timeout mili seconds");
        usleep(abs($timeout) * 1000);
    }
}
