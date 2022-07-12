<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior;

use CloudEvents\V1\CloudEventInterface;
use OpenTelemetry\API\Common\Event\Dispatcher;

trait EmitsEventsTrait
{
    protected static function emit(CloudEventInterface $event): void
    {
        Dispatcher::getInstance()->dispatch($event);
    }
}
