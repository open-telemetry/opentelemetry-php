<?php

declare(strict_types=1);

namespace OpenTelemetry\EventHandler;

interface ListenerInterface
{
    public static function handle(EventInterface $event):void;
}
