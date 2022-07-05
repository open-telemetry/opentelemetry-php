<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

use function array_key_exists;
use IteratorAggregate;
use Traversable;

final class Attributes implements AttributesInterface, IteratorAggregate
{
    private array $attributes;
    private int $droppedAttributesCount;

    /**
     * @internal
     */
    public function __construct(array $attributes, int $droppedAttributesCount)
    {
        $this->attributes = $attributes;
        $this->droppedAttributesCount = $droppedAttributesCount;
    }

    public static function create(iterable $attributes): AttributesInterface
    {
        return self::factory()->builder($attributes)->build();
    }

    public static function factory(?int $attributeCountLimit = null, ?int $attributeValueLengthLimit = null): AttributesFactoryInterface
    {
        return new AttributesFactory($attributeCountLimit, $attributeValueLengthLimit);
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /** @psalm-mutation-free */
    public function count(): int
    {
        return \count($this->attributes);
    }

    public function getIterator(): Traversable
    {
        foreach ($this->attributes as $key => $value) {
            yield (string) $key => $value;
        }
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function getDroppedAttributesCount(): int
    {
        return $this->droppedAttributesCount;
    }
}
