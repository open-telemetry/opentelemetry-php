<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Subscriber\Listener;

use OpenTelemetry\SDK\Subscriber\ListenerInterface;

class StartSpanListener implements ListenerInterface
{
    public function takeAction(array $array):void
    {
        $span = $array[0];
        $span->setAttribute('Listener', 'StartSpanListener');
    }
}
