<?php

declare(strict_types=1);

namespace OpenTelemetry\EventHandler\Event;

use OpenTelemetry\EventHandler\EventInterface;

class StartSpanEvent implements EventInterface
{
    private array $eventArray ;
    public function __construct(array $eventArray)
    {
        $this->eventArray = $eventArray;
    }

    public function getArray():array
    {
        return $this->eventArray;
    }

    public function getClassName():string
    {
        return 'StartSpanEvent';
    }
}
