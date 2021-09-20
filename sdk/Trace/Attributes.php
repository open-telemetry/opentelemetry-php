<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Sdk\Internal\StringUtil;
use OpenTelemetry\Trace as API;

class Attributes implements API\Attributes
{
    private $attributes = [];

    /** @var AttributeLimits */
    private $attributeLimits;

    /** @var int Counts for attributes dropped due to collection limits */
    private $droppedAttributeCount = 0;

    /** @return Attributes Returns a new instance of Attributes with the limits applied */
    public static function withLimits(API\Attributes $attributes, AttributeLimits $attributeLimits): Attributes
    {
        return new self($attributes->getIterator(), $attributeLimits);
    }

    public function __construct(iterable $attributes = [], AttributeLimits $attributeLimits = null)
    {
        $this->attributeLimits = $attributeLimits ?? new AttributeLimits();
        foreach ($attributes as $key => $attribute) {
            $attributeKey = $attribute instanceof Attribute ? $attribute->getKey() : (string) $key;
            $attributeValue = $attribute instanceof Attribute ? $attribute->getValue() : $attribute;
            $this->setAttribute($attributeKey, $attributeValue);
        }
    }

    public function setAttribute(string $name, $value): API\Attributes
    {
        // unset the attribute when null value is passed
        if (null === $value) {
            unset($this->attributes[$name]);

            return $this;
        }

        // drop attribute when limit is reached
        if (!isset($this->attributes[$name]) && $this->count() >= $this->attributeLimits->getAttributeCountLimit()) {
            $this->droppedAttributeCount++;

            return $this;
        }

        if (is_string($value)) {
            $limitedValue = StringUtil::substr($value, 0, $this->attributeLimits->getAttributeValueLengthLimit());
        } elseif (is_array($value)) {
            $limitedValue = array_map(function ($arrayValue) {
                if (is_string($arrayValue)) {
                    return StringUtil::substr($arrayValue, 0, $this->attributeLimits->getAttributeValueLengthLimit());
                }

                return $arrayValue;
            }, $value);
        } else {
            $limitedValue = $value;
        }

        $this->attributes[$name] = new Attribute($name, $limitedValue);

        return $this;
    }

    public function getAttribute(string $name): ?Attribute
    {
        return $this->attributes[$name] ?? null;
    }

    public function count(): int
    {
        return \count($this->attributes);
    }

    public function getIterator(): API\AttributesIterator
    {
        return new class($this->attributes) implements API\AttributesIterator {
            private $inner;
            public function __construct($attributes)
            {
                $this->inner = new \ArrayIterator($attributes);
            }

            public function key(): string
            {
                return (string) $this->inner->key();
            }

            public function current(): API\Attribute
            {
                return $this->inner->current();
            }

            public function rewind(): void
            {
                $this->inner->rewind();
            }

            public function valid(): bool
            {
                return $this->inner->valid();
            }

            public function next(): void
            {
                $this->inner->next();
            }
        };
    }

    public function getDroppedAttributesCount(): int
    {
        return $this->droppedAttributeCount;
    }
}
