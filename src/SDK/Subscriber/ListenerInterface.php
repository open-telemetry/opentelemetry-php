<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Subscriber;

interface ListenerInterface
{
    public function takeAction(array $array):void;
}
