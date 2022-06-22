<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Event\Handler;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Common\Event\Event\DebugEvent;

class DebugEventHandler
{
    use LogsMessagesTrait;

    public function __invoke(DebugEvent $event): void
    {
        self::logDebug($event->getMessage(), $event->getExtra());
    }
}
