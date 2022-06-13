<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Event\Handler;

use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Event\Event\ErrorEvent;

class ErrorEventHandler
{
    use LogsMessagesTrait;

    public function __invoke(ErrorEvent $event): void
    {
        self::logError($event->getError()->getMessage(), ['error' => $event->getError()]);
    }
}
