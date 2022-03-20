<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

use Countable;
use IteratorAggregate;
use Traversable;

interface AttributesInterface extends IteratorAggregate, Countable
{
    public function setAttribute(string $name, $value): AttributesInterface;
    public function unsetAttribute(string $name): AttributesInterface;
    public function hasAttribute(string $name): bool;
    public function get(string $name);

    public function getIterator(): Traversable;
    public function toArray(): array;

    public function getTotalAddedValues(): int;
    public function getDroppedAttributesCount(): int;

    public function isLimitReached(): bool;
}
