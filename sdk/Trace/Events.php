<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

class Events implements API\Events
{
    private $events = [];

    public function addEvent(
        string $name,
        ?API\Attributes $attributes = null,
        int $timestamp = null
    ): API\Events {
        $this->events[] = new Event($name, $timestamp ?? time(), $attributes);

        return $this;
    }

    public function count(): int
    {
        return \count($this->events);
    }

    public function getIterator(): API\EventsIterator
    {
        return new class($this->events) implements API\EventsIterator {
            private $inner;
            public function __construct($events)
            {
                $this->inner = new \ArrayIterator($events);
            }

            public function key(): int
            {
                return $this->inner->key();
            }

            public function current(): API\Event
            {
                return $this->inner->current();
            }

            public function rewind(): void
            {
                $this->inner->rewind();
            }

            public function valid(): bool
            {
                return $this->inner->valid();
            }

            public function next(): void
            {
                $this->inner->next();
            }
        };
    }
}
