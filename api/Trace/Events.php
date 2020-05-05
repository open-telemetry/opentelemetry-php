<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

use OpenTelemetry\Trace as API;

interface Events extends \IteratorAggregate, \Countable
{
    /**
     * Adding an event should not invalidate nor change any existing iterators.
     * @param string $name
     * @param Attributes|null $attributes
     * @param API\Clock|null $moment
     * @return Events Must return $this to allow setting multiple attributes at once in a chain.
     */
    public function addEvent(string $name, ?Attributes $attributes = null, ?API\Clock $moment = null): Events;

    public function count(): int;
    public function getIterator(): EventsIterator;
}
