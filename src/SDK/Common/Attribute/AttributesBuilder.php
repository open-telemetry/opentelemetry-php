<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

use function array_key_exists;
use function count;
use function get_resource_type;
use function is_array;
use function is_object;
use function is_resource;
use function is_scalar;
use function is_string;
use function method_exists;
use function serialize;
use function substr;
use Throwable;

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

    public function incrementDroppedAttributesCount(int $count = 1): AttributesBuilderInterface
    {
        $this->droppedAttributesCount += $count;

        return $this;
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

    public function offsetSet($offset, $value)
    {
        if ($value === null) {
            return;
        }
        if ($offset === null) {
            return;
        }
        if (count($this->attributes) === $this->attributeCountLimit && !array_key_exists($offset, $this->attributes)) {
            $this->droppedAttributesCount++;

            return;
        }

        $this->attributes[$offset] = self::convert($value, $this->attributeValueLengthLimit);
    }

    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    private static function convert($value, ?int $attributeValueLengthLimit)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $processed = self::convert($v, $attributeValueLengthLimit);
                if ($processed !== $v) {
                    $value[$k] = $processed;
                }
            }
        } else {
            $value = self::convertValue($value);

            if (is_string($value) && $attributeValueLengthLimit !== null) {
                $value = substr($value, 0, $attributeValueLengthLimit);
            }
        }

        return $value;
    }

    private static function convertValue($value)
    {
        if (is_scalar($value) || $value === null) {
            return $value;
        }
        if (is_resource($value)) {
            return get_resource_type($value);
        }
        /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
        /** @psalm-suppress UndefinedClass @phpstan-ignore-next-line @phan-suppress-next-line PhanUndeclaredClassInstanceof */
        if ($value instanceof \UnitEnum) {
            /** @phpstan-ignore-next-line @phan-suppress-next-line PhanUndeclaredClassProperty */
            return $value->name;
        }
        if (is_object($value) && method_exists($value, '__toString')) {
            try {
                return $value->__toString();
            } catch (Throwable $e) {
            }
        }

        try {
            return serialize($value);
        } catch (Throwable $e) {
        }

        return null;
    }
}
