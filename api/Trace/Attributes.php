<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Attributes extends \IteratorAggregate, \Countable
{
    public function setAttribute(string $name, $value): Attributes;
    public function getAttribute(string $name): ?Attribute;
    public function get(string $name);

    public function count(): int;
    public function getIterator(): AttributesIterator;
}
