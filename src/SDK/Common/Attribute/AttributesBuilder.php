<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

use function array_key_exists;
use function count;
use function is_array;
use function is_string;
use function mb_substr;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;

/**
 * @internal
 */
final class AttributesBuilder implements AttributesBuilderInterface
{
    use LogsMessagesTrait;

    public function __construct(private array $attributes, private ?int $attributeCountLimit, private ?int $attributeValueLengthLimit, private int $droppedAttributesCount, private AttributeValidatorInterface $attributeValidator = new AttributeValidator())
    {
    }

    public function build(): AttributesInterface
    {
        return new Attributes($this->attributes, $this->droppedAttributesCount);
    }

    public function merge(AttributesInterface $old, AttributesInterface $updating): AttributesInterface
    {
        $new = $old->toArray();
        $dropped = $old->getDroppedAttributesCount() + $updating->getDroppedAttributesCount();
        foreach ($updating->toArray() as $key => $value) {
            if (count($new) === $this->attributeCountLimit && !array_key_exists($key, $new)) {
                $dropped++;
            } else {
                $new[$key] = $value;
            }
        }

        return new Attributes($new, $dropped);
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->attributes);
    }

    /**
     * @phan-suppress PhanUndeclaredClassAttribute
     */
    public function offsetGet($offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    /**
     * @phan-suppress PhanUndeclaredClassAttribute
     */
    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            return;
        }
        if ($value === null) {
            unset($this->attributes[$offset]);

            return;
        }
        if (!$this->attributeValidator->validate($value)) {
            self::logWarning($this->attributeValidator->getInvalidMessage() . ': ' . $offset);
            $this->droppedAttributesCount++;

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
    public function offsetUnset($offset): void
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
