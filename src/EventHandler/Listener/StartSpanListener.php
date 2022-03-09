<?php

declare(strict_types=1);

namespace OpenTelemetry\EventHandler\Listener;

use OpenTelemetry\EventHandler\EventInterface;
use OpenTelemetry\EventHandler\ListenerInterface;

class StartSpanListener implements ListenerInterface
{
    public static function handle(EventInterface $event):void
    {
        $eventArray = $event->getArray();
        $span = $eventArray[0];
        $span->setAttribute('Listener', 'StartSpanListener');
    }
}
