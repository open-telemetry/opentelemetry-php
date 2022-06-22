<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

use Countable;
use Traversable;

interface AttributesInterface extends Traversable, Countable
{
    public function setAttribute(string $name, $value): AttributesInterface;
    public function unsetAttribute(string $name): AttributesInterface;
    public function has(string $name): bool;
    public function get(string $name);

    public function toArray(): array;

    public function getDroppedAttributesCount(): int;
}
