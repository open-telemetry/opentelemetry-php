<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

use function mb_substr;
use Traversable;

class Attributes implements AttributesInterface
{
    private array $attributes = [];

    private AttributeLimitsInterface $attributeLimits;

    private int $totalAddedAttributes = 0;

    /** @return Attributes Returns a new instance of Attributes with the limits applied */
    public static function withLimits(iterable $attributes, AttributeLimitsInterface $attributeLimits): Attributes
    {
        return new self($attributes, $attributeLimits);
    }

    public function __construct(iterable $attributes = [], AttributeLimitsInterface $attributeLimits = null)
    {
        $this->attributeLimits = $attributeLimits ?? new AttributeLimits();
        foreach ($attributes as $key => $value) {
            $this->setAttribute((string) $key, $value);
        }
    }

    public function setAttribute(string $name, $value): AttributesInterface
    {
        $this->totalAddedAttributes++;

        // unset the attribute when null value is passed
        if (null === $value) {
            unset($this->attributes[$name]);

            $this->totalAddedAttributes--;

            return $this;
        }

        // drop attribute when limit is reached
        if (!isset($this->attributes[$name]) && count($this) >= $this->attributeLimits->getAttributeCountLimit()) {
            return $this;
        }

        if (is_string($value)) {
            $limitedValue = mb_substr($value, 0, $this->attributeLimits->getAttributeValueLengthLimit());
        } elseif (is_array($value)) {
            $limitedValue = array_map(function ($arrayValue) {
                if (is_string($arrayValue)) {
                    return mb_substr($arrayValue, 0, $this->attributeLimits->getAttributeValueLengthLimit());
                }

                return $arrayValue;
            }, $value);
        } else {
            $limitedValue = $value;
        }

        $this->attributes[$name] = $limitedValue;

        return $this;
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

    public function getTotalAddedValues(): int
    {
        return $this->totalAddedAttributes;
    }

    public function getDroppedAttributesCount(): int
    {
        return $this->totalAddedAttributes - count($this);
    }
}
