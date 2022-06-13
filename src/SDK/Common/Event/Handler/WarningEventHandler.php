<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Event\Handler;

use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Event\Event\WarningEvent;

class WarningEventHandler
{
    use LogsMessagesTrait;

    public function __invoke(WarningEvent $event): void
    {
        self::logWarning($event->getMessage(), ['error' => $event->getError()]);
    }
}
