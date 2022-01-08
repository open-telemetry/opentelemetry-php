<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use function count;
use IteratorAggregate;
use Traversable;

/**
 * @psalm-immutable
 */
final class Attributes implements AttributesInterface, IteratorAggregate
{
    /** @var array<non-empty-string|int, bool|int|float|string|array> */
    private array $attributes;
    private int $droppedAttributesCount;

    /**
     * @param array<non-empty-string|int, bool|int|float|string|array> $attributes
     */
    public function __construct(array $attributes = [], int $droppedAttributesCount = 0)
    {
        $this->attributes = $attributes;
        $this->droppedAttributesCount = $droppedAttributesCount;
    }

    /**
     * @param iterable<non-empty-string, bool|int|float|string|array|null> $attributes
     */
    public static function create(iterable $attributes = []): AttributesInterface
    {
        return AttributesBuilder::from($attributes)->build();
    }

    public static function factory(?int $attributeCountLimit = null, ?int $attributeValueLengthLimit = null): AttributesFactoryInterface
    {
        return new AttributesFactory($attributeCountLimit, $attributeValueLengthLimit);
    }

    public function get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function getDroppedAttributesCount(): int
    {
        return $this->droppedAttributesCount;
    }

    public function count(): int
    {
        return count($this->attributes);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function getIterator(): Traversable
    {
        foreach ($this->attributes as $key => $value) {
            yield (string) $key => $value;
        }
    }
}
