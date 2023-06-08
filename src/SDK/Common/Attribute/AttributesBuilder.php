<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

use function array_key_exists;
use function count;
use function gettype;
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

    private array $attributes;
    private ?int $attributeCountLimit;
    private ?int $attributeValueLengthLimit;
    private int $droppedAttributesCount;
    private const NUMERICS = ['integer', 'double'];

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
        if (is_array($value) && !$this->isHomogeneous($value)) {
            self::logWarning('attribute with non-homogeneous array values dropped: ' . $offset);

            return;
        }
        if (count($this->attributes) === $this->attributeCountLimit && !array_key_exists($offset, $this->attributes)) {
            $this->droppedAttributesCount++;

            return;
        }

        $this->attributes[$offset] = $this->normalizeValue($value);
        //@todo "There SHOULD be a message printed in the SDK's log to indicate to the user that an attribute was
        //       discarded due to such a limit. To prevent excessive logging, the message MUST be printed at most
        //       once per <thing> (i.e., not per discarded attribute)."
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

    /**
     * Test whether an array contains only values of the same type, treating int/double as equivalent.
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.21.0/specification/common/README.md#attribute
     */
    private function isHomogeneous(array $value): bool
    {
        if (count($value) <= 1) {
            return true;
        }
        $type = gettype($value[0]);
        foreach ($value as $v) {
            if (in_array(gettype($v), self::NUMERICS) && in_array($type, self::NUMERICS)) {
                continue;
            }
            if (gettype($v) !== $type) {
                return false;
            }
        }

        return true;
    }
}
