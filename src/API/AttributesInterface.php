<?php

declare(strict_types=1);

namespace OpenTelemetry\API;

use Countable;
use IteratorAggregate;

interface AttributesInterface extends IteratorAggregate, Countable
{
    public function setAttribute(string $name, $value): AttributesInterface;
    public function getAttribute(string $name): ?AttributeInterface;
    public function get(string $name);

    public function getIterator(): AttributesIteratorInterface;

    public function getTotalAddedValues(): int;
    public function getDroppedAttributesCount(): int;
}
