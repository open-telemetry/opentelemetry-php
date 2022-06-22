<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Event\Handler;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Common\Event\Event\WarningEvent;

class WarningEventHandler
{
    use LogsMessagesTrait;

    public function __invoke(WarningEvent $event): void
    {
        self::logWarning($event->getMessage(), ['exception' => $event->getException()]);
    }
}
