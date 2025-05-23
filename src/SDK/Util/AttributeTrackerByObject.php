<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Util;

class AttributeTrackerByObject
{
    protected \WeakMap $attributes;

    public function __construct()
    {
        $this->attributes = new \WeakMap();
    }

    public function has(object $id): bool
    {
        return $this->attributes->offsetExists($id);
    }

    public function set(object $id, array $attributes): void
    {
        $this->attributes[$id] = $attributes;
    }

    public function add(object $id, array $attributes): array
    {
        if ($this->attributes->offsetExists($id) === false) {
            return $this->attributes[$id] = $attributes;

        }

        return $this->attributes[$id] = [...$this->attributes[$id], ...$attributes];
    }

    public function get(object $id): array
    {
        if ($this->attributes->offsetExists($id) === false) {
            return [];
        }

        return $this->attributes[$id];
    }

    public function append(object $id, string|int $key, mixed $value): void
    {
        $attributes = $this->attributes[$id] ?? [];
        $attributes[$key] = $value;
        $this->attributes[$id] = $attributes;
    }

    public function clear(object $id): void
    {
        unset($this->attributes[$id]);
    }

    public function reset(): void
    {
        $this->attributes = new \WeakMap();
    }
}
