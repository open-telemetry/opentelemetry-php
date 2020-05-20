<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

class Event implements API\Event
{
    private $name;
    private $timestamp;
    private $attributes;

    public function __construct(string $name, int $timestamp, ?API\Attributes $attributes = null)
    {
        $this->name = $name;
        $this->timestamp = $timestamp;
        $this->attributes = $attributes ?? new Attributes();
    }

    public function setAttribute(string $key, $value): self
    {
        $this->attributes->setAttribute($key, $value);

        return $this;
    }

    public function getAttributes(): API\Attributes
    {
        return $this->attributes;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}
