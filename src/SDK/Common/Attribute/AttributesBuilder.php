<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

use function array_key_exists;
use function count;
use function is_array;
use function is_string;
use function mb_substr;

/**
 * @internal
 */
final class AttributesBuilder implements AttributesBuilderInterface
{
    private array $attributes;
    private ?int $attributeCountLimit;
    private ?int $attributeValueLengthLimit;
    private int $droppedAttributesCount;

    public function __construct(array $attributes, ?int $attributeCountLimit, ?int $attributeValueLengthLimit, int $droppedAttributesCount)
    {
        $this->attributes = $attributes;
        $this->attributeCountLimit = $attributeCountLimit;
        $this->attributeValueLengthLimit = $attributeValueLengthLimit;
        $this->droppedAttributesCount = $droppedAttributesCount;
    }

    public function build(): AttributesInterface
    {
        return new Attributes($this->attributes, $this->droppedAttributesCount);
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->attributes);
    }

    /**
     * @phan-suppress PhanUndeclaredClassAttribute
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->attributes[$offset] ?? null;
    }

    /**
     * @phan-suppress PhanUndeclaredClassAttribute
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            return;
        }
        if ($value === null) {
            unset($this->attributes[$offset]);

            return;
        }
        if (count($this->attributes) === $this->attributeCountLimit && !array_key_exists($offset, $this->attributes)) {
            $this->droppedAttributesCount++;

            return;
        }

        $this->attributes[$offset] = $this->normalizeValue($value);
    }

    /**
     * @phan-suppress PhanUndeclaredClassAttribute
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    private function normalizeValue($value)
    {
        if (is_string($value) && $this->attributeValueLengthLimit !== null) {
            return mb_substr($value, 0, $this->attributeValueLengthLimit);
        }

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $processed = $this->normalizeValue($v);
                if ($processed !== $v) {
                    $value[$k] = $processed;
                }
            }

            return $value;
        }

        return $value;
    }
}
