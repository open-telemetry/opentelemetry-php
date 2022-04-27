<?php

declare(strict_types=1);

namespace OpenTelemetry\EventHandler;

interface EventInterface
{
    public function getTarget():object;

    public function getEventName():string;
}
