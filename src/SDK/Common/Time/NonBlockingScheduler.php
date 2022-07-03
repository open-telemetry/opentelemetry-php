<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

require __DIR__ . '/../../../../vendor/autoload.php';

use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use Revolt\EventLoop;

/**
 * @internal
 *
 * @phan-file-suppress PhanUndeclaredClassReference
 * @phan-file-suppress PhanUndeclaredClassMethod
 * @phan-file-suppress PhanAccessMethodInternal
 */
class NonBlockingScheduler implements SchedulerInterface
{
    use LogsMessagesTrait;
    public function __construct()
    {
        self::logInfo('setting NonBlocking Delay scheduler');
    }

    public function delay(int $timeout): void
    {
        $suspension = EventLoop::getSuspension();
        self::logInfo("Delaying the execution by $timeout mili seconds");
        EventLoop::delay((float) ($timeout/1000), fn () => $suspension->resume());
        self::logInfo('Resuming the execution after delay');
        $suspension->suspend();
    }
}
