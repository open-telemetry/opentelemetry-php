<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Event\Handler;

use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Event\Event\DebugEvent;

class DebugEventHandler
{
    use LogsMessagesTrait;

    public function __invoke(DebugEvent $event): void
    {
        self::logDebug($event->getMessage());
    }
}
