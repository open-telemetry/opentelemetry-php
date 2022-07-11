<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior;

use OpenTelemetry\API\Common\Event\Dispatcher;

trait EmitsEventsTrait
{
    protected static function emit(object $event): void
    {
        Dispatcher::getInstance()->dispatch($event);
    }
}
