<?php

declare(strict_types=1);

namespace OpenTelemetry\EventHandler\Event;

use OpenTelemetry\EventHandler\EventInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;

class EndSpanEvent implements EventInterface
{
    private ReadableSpanInterface $target ;
    public function __construct(ReadableSpanInterface $target)
    {
        $this->target = $target;
    }

    public function getTarget():ReadableSpanInterface
    {
        return $this->target;
    }

    public function getEventName():string
    {
        return 'EndSpanEvent';
    }
}
