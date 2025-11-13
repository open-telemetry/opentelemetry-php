<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

class AttributeTrackerById
{
    /**
     * @var array<non-empty-string, mixed>
     */
    protected array $attributes = [];

    public function has(string|int $id): bool
    {
        return isset($this->attributes[$id]);
    }

    /**
     * @param array<non-empty-string, mixed> $attributes
     */
    public function set(string|int $id, array $attributes): self
    {
        $this->attributes[$id] = $attributes;

        return $this;
    }

    /**
     * @param array<non-empty-string, mixed> $attributes
     * @return array<non-empty-string, mixed>
     */
    public function add(string|int $id, array $attributes): array
    {
        if (!isset($this->attributes[$id])) {
            return $this->attributes[$id] = $attributes;
        }

        return $this->attributes[$id] = [...$this->attributes[$id], ...$attributes];
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    public function get(string|int $id): array
    {
        if (!isset($this->attributes[$id])) {
            return [];
        }

        return $this->attributes[$id];
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    public function append(string|int $id, string|int $key, mixed $value): array
    {
        $this->attributes[$id] ??= [];
        $this->attributes[$id][$key] = $value;

        return $this->attributes[$id];
    }

    public function clear(string|int $id): void
    {
        unset($this->attributes[$id]);
    }

    public function reset(): void
    {
        $this->attributes = [];
    }
}
