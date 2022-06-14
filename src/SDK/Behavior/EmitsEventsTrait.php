<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Behavior;

use OpenTelemetry\SDK\Common\Event\Dispatcher;

trait EmitsEventsTrait
{
    protected static function emit(object $event): void
    {
        Dispatcher::getInstance()->dispatch($event);
    }
}
