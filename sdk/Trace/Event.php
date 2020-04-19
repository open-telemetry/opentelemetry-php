<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Sdk\Internal\Timestamp;
use OpenTelemetry\Trace as API;

class Event implements API\Event
{
    private $name;
    private $timestamp;
    private $attributes;

    public function __construct(string $name, ?API\Attributes $attributes = null, API\Timestamp $timestamp = null)
    {
        if (null === $timestamp) {
            $timestamp = Timestamp::now();
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

    public function getTimestamp(): API\Timestamp
    {
        return $this->timestamp;
    }
}
