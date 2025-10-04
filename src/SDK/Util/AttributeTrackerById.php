<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Util;

class AttributeTrackerById
{
    protected array $attributes = [];

    public function has(string|int $id): bool
    {
        return isset($this->attributes[$id]);
    }

    public function set(string|int $id, array $attributes): void
    {
        $this->attributes[$id] = $attributes;
    }

    public function add(string|int $id, array $attributes): array
    {
        if (!isset($this->attributes[$id])) {
            return $this->attributes[$id] = $attributes;

        }

        return $this->attributes[$id] = [...$this->attributes[$id], ...$attributes];
    }

    public function get(string|int $id): array
    {
        if (!isset($this->attributes[$id])) {
            return [];
        }

        return $this->attributes[$id];
    }

    public function append(string|int $id, string|int $key, mixed $value): void
    {
        $this->attributes[$id] ??= [];
        $this->attributes[$id][$key] = $value;
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
