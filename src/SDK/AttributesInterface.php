<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use Countable;
use Traversable;

/**
 * @psalm-immutable
 *
 * @template-extends Traversable<non-empty-string, bool|int|float|string|array>
 */
interface AttributesInterface extends Traversable, Countable
{
    /**
     * @param non-empty-string $name
     * @return bool|int|float|string|array|null
     */
    public function get(string $name);

    public function getDroppedAttributesCount(): int;

    /**
     * @return array<non-empty-string|int, bool|int|float|string|array>
     */
    public function toArray(): array;
}
