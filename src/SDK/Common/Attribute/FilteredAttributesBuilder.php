<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

use function in_array;

/**
 * @internal
 */
final class FilteredAttributesBuilder implements AttributesBuilderInterface
{
    private int $rejected = 0;

    /**
     * @param list<string> $rejectedKeys
     */
    public function __construct(
        private AttributesBuilderInterface $builder,
        private readonly array $rejectedKeys,
    ) {
    }

    public function __clone()
    {
        $this->builder = clone $this->builder;
    }

    public function build(): AttributesInterface
    {
        $attributes = $this->builder->build();
        $dropped = $attributes->getDroppedAttributesCount() + $this->rejected;

        return new Attributes($attributes->toArray(), $dropped);
    }

    public function merge(AttributesInterface $old, AttributesInterface $updating): AttributesInterface
    {
        return $this->builder->merge($old, $updating);
    }

    public function offsetExists($offset): bool
    {
        return $this->builder->offsetExists($offset);
    }

    /**
     * @phan-suppress PhanUndeclaredClassAttribute
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->builder->offsetGet($offset);
    }

    /**
     * @phan-suppress PhanUndeclaredClassAttribute
     */
    public function offsetSet($offset, $value): void
    {
        if ($value !== null && in_array($offset, $this->rejectedKeys, true)) {
            $this->rejected++;

            return;
        }

        $this->builder->offsetSet($offset, $value);
    }

    /**
     * @phan-suppress PhanUndeclaredClassAttribute
     */
    public function offsetUnset($offset): void
    {
        $this->builder->offsetUnset($offset);
    }
}
