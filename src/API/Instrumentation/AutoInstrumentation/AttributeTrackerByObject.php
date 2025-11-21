<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

use WeakMap;

class AttributeTrackerByObject
{
    /**
     * @var WeakMap<object, array<non-empty-string, mixed>>
     */
    protected WeakMap $attributes;

    public function __construct()
    {
        /** @psalm-suppress PropertyTypeCoercion */
        $this->attributes = new WeakMap();
    }

    public function has(object $id): bool
    {
        return $this->attributes->offsetExists($id);
    }

    /**
     * @param array<non-empty-string, mixed> $attributes
     */
    public function set(object $id, array $attributes): self
    {
        $this->attributes[$id] = $attributes;

        return $this;
    }

    /**
     * @param array<non-empty-string, mixed> $attributes
     * @return array<non-empty-string, mixed>
     */
    public function add(object $id, array $attributes): array
    {
        if ($this->attributes->offsetExists($id) === false) {
            return $this->attributes[$id] = $attributes;
        }

        return $this->attributes[$id] = [...$this->attributes[$id], ...$attributes];
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    public function get(object $id): array
    {
        if ($this->attributes->offsetExists($id) === false) {
            return [];
        }

        return $this->attributes[$id];
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    public function append(object $id, string|int $key, mixed $value): array
    {
        /** @var array<non-empty-string, mixed> $attributes */
        $attributes = $this->attributes[$id] ?? [];
        $attributes[$key] = $value;

        return $this->attributes[$id] = $attributes;
    }

    public function clear(object $id): void
    {
        unset($this->attributes[$id]);
    }

    public function reset(): void
    {
        $this->attributes = new WeakMap();
    }
}
