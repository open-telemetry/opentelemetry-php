<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use Countable;
use Traversable;

interface AttributesInterface extends Traversable, Countable
{
    public function get(string $name);

    /** @psalm-mutation-free */
    public function getDroppedAttributesCount(): int;

    public function toArray(): array;
}
