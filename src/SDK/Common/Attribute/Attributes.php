<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

use function count;
use IteratorAggregate;
use function mb_substr;
use Traversable;

class Attributes implements AttributesInterface, IteratorAggregate
{
    private array $attributes = [];

    private AttributeLimitsInterface $attributeLimits;

    private int $totalAddedAttributes = 0;

    /**
     * @internal
     */
    public function __construct(iterable $attributes = [], AttributeLimitsInterface $attributeLimits = null)
    {
        $this->attributeLimits = $attributeLimits ?? new AttributeLimits();
        foreach ($attributes as $key => $value) {
            $this->setAttribute((string) $key, $value);
        }
    }

    public static function create(iterable $attributes): AttributesInterface
    {
        return self::withLimits($attributes, new AttributeLimits());
    }

    /** @return Attributes Returns a new instance of Attributes with the limits applied */
    public static function withLimits(iterable $attributes, AttributeLimitsInterface $attributeLimits): Attributes
    {
        return new self($attributes, $attributeLimits);
    }

    public function has(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function setAttribute(string $name, $value): AttributesInterface
    {
        // unset the attribute when null value is passed
        if ($value === null) {
            return $this->unsetAttribute($name);
        }

        if (!$this->has($name)) {
            $this->totalAddedAttributes++;
        }
        // drop attribute when limit is reached
        if (!$this->has($name) && count($this) >= $this->attributeLimits->getAttributeCountLimit()) {
            return $this;
        }

        $this->attributes[$name] = $this->normalizeValue($value);

        return $this;
    }

    public function unsetAttribute(string $name): AttributesInterface
    {
        if ($this->has($name)) {
            unset($this->attributes[$name]);

            $this->totalAddedAttributes--;
        }

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

    public function getDroppedAttributesCount(): int
    {
        return $this->totalAddedAttributes - count($this);
    }

    private function truncateStringValue(string $value): string
    {
        return mb_substr($value, 0, $this->attributeLimits->getAttributeValueLengthLimit());
    }

    private function normalizeValue($value)
    {
        if (is_string($value)) {
            return  $this->truncateStringValue($value);
        }

        if (is_array($value)) {
            return array_map(function ($value) {
                return $this->normalizeValue($value);
            }, $value);
        }

        return $value;
    }
}
