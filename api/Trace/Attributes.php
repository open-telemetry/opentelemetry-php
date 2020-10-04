<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Attributes extends \IteratorAggregate, \Countable
{
    /**
     * Setting event should not invalidate nor change any existing iterators.
     * @param string $name
     * @param mixed  $value
     * @return Attributes
     */
    public function setAttribute(string $name, $value): Attributes;

    public function count(): int;
    public function getIterator(): AttributesIterator;
}
