<?php

declare(strict_types=1);

namespace OpenTelemetry\EventHandler;

interface EventInterface
{
    public function getArray():array;

    public function getClassName():string;
}
