<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Events extends \IteratorAggregate, \Countable
{
    /**
     * Adding an event should not invalidate nor change any existing iterators.
     * @param string $name
     * @param Attributes|null $attributes
     * @param int|null $timestamp
     * @return Events Must return $this to allow setting multiple attributes at once in a chain.
     */
    public function addEvent(string $name, ?Attributes $attributes = null, ?int $timestamp = null): Events;

    public function count(): int;
    public function getIterator(): EventsIterator;
}
