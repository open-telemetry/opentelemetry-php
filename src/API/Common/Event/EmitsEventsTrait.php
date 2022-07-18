<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Event;

use CloudEvents\V1\CloudEventInterface;

trait EmitsEventsTrait
{
    protected static function emit(CloudEventInterface $event): void
    {
        Dispatcher::getInstance()->dispatch($event);
    }
}
