<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Subscriber\Listener;

use OpenTelemetry\SDK\Subscriber\ListenerInterface;

class EndSpanListener implements ListenerInterface
{
    public function takeAction(array $array):void
    {
        $span = $array[0];
        echo 'End Span Attribute can be used';
    }
}
