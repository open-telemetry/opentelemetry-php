<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Subscriber\Event;

use OpenTelemetry\SDK\Subscriber\EventInterface;

class EndSpanEvent implements EventInterface
{
    private array $array ;
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function getObject():array
    {
        return $this->array;
    }
}
