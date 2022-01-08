<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use function array_key_exists;
use function array_splice;
use function count;
use function is_array;
use function is_string;
use function mb_strlen;
use function mb_substr;

/**
 * @internal
 */
final class AttributesBuilder implements AttributesBuilderInterface
{
    /** @var array<non-empty-string|int, bool|int|float|string|array> */
    private array $attributes;
    private ?int $attributeCountLimit;
    private ?int $attributeValueLengthLimit;
    private int $droppedAttributesCount;

    /**
     * @param array<non-empty-string|int, bool|int|float|string|array> $attributes
     */
    private function __construct(array $attributes, ?int $attributeCountLimit, ?int $attributeValueLengthLimit, int $droppedAttributesCount)
    {
        $this->attributes = $attributes;
        $this->attributeCountLimit = $attributeCountLimit;
        $this->attributeValueLengthLimit = $attributeValueLengthLimit;
        $this->droppedAttributesCount = $droppedAttributesCount;
    }

    /**
     * @param iterable<non-empty-string, bool|int|float|string|array|null> $attributes
     */
    public static function from(iterable $attributes, ?int $attributeCountLimit = null, ?int $attributeValueLengthLimit = null): AttributesBuilderInterface
    {
        if (!$attributes) {
            return new self([], $attributeCountLimit, $attributeValueLengthLimit, 0);
        }

        $droppedAttributesCount = $attributes instanceof AttributesInterface
            ? $attributes->getDroppedAttributesCount()
            : 0;

        if ($attributes instanceof Attributes) {
            $attributes = $attributes->toArray();
        } elseif (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                if ($value === null) {
                    unset($attributes[$key]);
                }
            }
        }

        if (is_array($attributes)) {
            if ($attributeCountLimit !== null && count($attributes) > $attributeCountLimit) {
                $droppedAttributesCount += count($attributes) - $attributeCountLimit;
                array_splice($attributes, $attributeCountLimit);
            }
            if ($attributeValueLengthLimit !== null) {
                foreach ($attributes as $key => $value) {
                    $limited = self::applyLengthLimit($value, $attributeValueLengthLimit);
                    if ($limited === $value) {
                        continue;
                    }

                    $attributes[$key] = $limited;
                }
            }

            /** @var array<non-empty-string|int, bool|int|float|string|array> $attributes */
            return new self($attributes, $attributeCountLimit, $attributeValueLengthLimit, $droppedAttributesCount);
        }

        /** @var iterable<non-empty-string, bool|int|float|string|array|null> $attributes */
        $sdkAttributes = new self([], $attributeCountLimit, $attributeValueLengthLimit, $droppedAttributesCount);
        foreach ($attributes as $key => $value) {
            $sdkAttributes[$key] = $value;
        }

        return $sdkAttributes;
    }

    public function build(): AttributesInterface
    {
        return new Attributes($this->attributes, $this->droppedAttributesCount);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->attributes[$offset] ?? null;
    }

    /**
     * @param non-empty-string $offset
     * @param bool|int|float|string|array|null $value
     */
    public function offsetSet($offset, $value): void
    {
        if ($value === null) {
            unset($this->attributes[$offset]);

            return;
        }

        if (count($this->attributes) === $this->attributeCountLimit && !array_key_exists($offset, $this->attributes)) {
            $this->droppedAttributesCount++;

            return;
        }
        if ($this->attributeValueLengthLimit !== null) {
            $value = self::applyLengthLimit($value, $this->attributeValueLengthLimit);
        }

        $this->attributes[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

    private static function applyLengthLimit($value, int $lengthLimit)
    {
        if (is_string($value)) {
            $value = mb_substr($value, 0, $lengthLimit);
        } elseif (is_array($value)) {
            foreach ($value as $k => $v) {
                if (is_string($v) && mb_strlen($v) > $lengthLimit) {
                    $value[$k] = mb_substr($v, 0, $lengthLimit);
                }
            }
        }

        return $value;
    }
}
