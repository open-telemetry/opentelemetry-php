<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Sdk\Internal\Clock;
use OpenTelemetry\Trace as API;

class Event implements API\Event
{
    private $name;
    private $timestamp;
    private $attributes;

    // todo: pick datatype for timestamp
    public function __construct(string $name, ?API\Attributes $attributes = null, int $timestamp = null)
    {
        if (null === $timestamp) {
            $timestamp = (new Clock())->zipkinFormattedTime();
        }
        $this->name = $name;
        $this->timestamp = $timestamp;
        $this->attributes = $attributes ?? new Attributes();
    }

    public function setAttribute(string $key, $value) : self
    {
        $this->attributes->setAttribute($key, $value);

        return $this;
    }

    public function getAttributes(): API\Attributes
    {
        return $this->attributes;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}
