<?php

declare(strict_types=1);

namespace OpenTelemetry\EventHandler\Event;

use OpenTelemetry\EventHandler\EventInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;

class StartSpanEvent implements EventInterface
{
    private ReadWriteSpanInterface $target ;
    public function __construct(ReadWriteSpanInterface $target)
    {
        $this->target = $target;
    }

    public function getTarget():ReadWriteSpanInterface
    {
        return $this->target;
    }

    public function getEventName():string
    {
        return 'StartSpanEvent';
    }
}
