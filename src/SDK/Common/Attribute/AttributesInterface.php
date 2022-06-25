<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

use Countable;
use Traversable;

interface AttributesInterface extends Traversable, Countable
{
    public function has(string $name): bool;

    public function get(string $name);

    public function getDroppedAttributesCount(): int;

    public function toArray(): array;
}
