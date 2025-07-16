<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

use function array_key_exists;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @psalm-suppress MissingTemplateParam
 */
final class Attributes implements AttributesInterface, IteratorAggregate, JsonSerializable
{
    /**
     * @internal
     */
    public function __construct(
        private readonly array $attributes,
        private readonly int $droppedAttributesCount,
    ) {
    }

    public static function create(iterable $attributes): AttributesInterface
    {
        return self::factory()->builder($attributes)->build();
    }

    public static function factory(?int $attributeCountLimit = null, ?int $attributeValueLengthLimit = null): AttributesFactoryInterface
    {
        return new AttributesFactory($attributeCountLimit, $attributeValueLengthLimit);
    }

    #[\Override]
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    #[\Override]
    public function get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /** @psalm-mutation-free */
    #[\Override]
    public function count(): int
    {
        return \count($this->attributes);
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->attributes;
    }

    #[\Override]
    public function getIterator(): Traversable
    {
        foreach ($this->attributes as $key => $value) {
            yield (string) $key => $value;
        }
    }

    #[\Override]
    public function toArray(): array
    {
        return $this->attributes;
    }

    #[\Override]
    public function getDroppedAttributesCount(): int
    {
        return $this->droppedAttributesCount;
    }
}
