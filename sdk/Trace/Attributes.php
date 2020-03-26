<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

class Attributes implements API\Attributes
{
    private $attributes = [];

    public function __construct(iterable $attributes = [])
    {
        foreach ($attributes as $key => $attribute) {
            $this->setAttribute($key, $attribute);
        }
    }

    public function setAttribute(string $name, $value): API\Attributes
    {
        if (isset($value)) {
            $this->attributes[$name] = new Attribute($name, $value);
        } else {
            // todo: does this warn?
            unset($this->attributes[$name]);
        }

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
                return $this->inner->key();
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
}
