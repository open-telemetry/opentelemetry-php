<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Event\Handler;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Common\Event\Event\ErrorEvent;

class ErrorEventHandler
{
    use LogsMessagesTrait;

    public function __invoke(ErrorEvent $event): void
    {
        self::logError($event->getMessage(), ['exception' => $event->getException()]);
    }
}
