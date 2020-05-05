<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

class Event implements API\Event
{
    private $name;
    private $moment;
    private $attributes;

    public function __construct(string $name, ?API\Attributes $attributes = null, API\Clock $moment = null)
    {
        if (null === $moment) {
            $moment = (new Clock())->moment();
        }
        $this->name = $name;
        $this->moment = $moment;
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

    public function getTimestamp(): API\Clock
    {
        return $this->moment;
    }
}
