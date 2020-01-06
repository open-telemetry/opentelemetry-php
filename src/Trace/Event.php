<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

class Event
{
    private $name;
    private $timestamp;
    private $attributes = [];

    public function __construct(string $name, iterable $attributes = [], $timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = microtime(true);
        }
        $this->name = $name;
        $this->timestamp = $timestamp;
        $this->setAttributes($attributes);
    }

    public function getAttribute(string $key)
    {
        if (!array_key_exists($key, $this->attributes)) {
            return null;
        }
        return $this->attributes[$key];
    }

    public function setAttribute(string $key, $value) : self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function getAttributes() : array
    {
        return $this->attributes;
    }

    public function setAttributes(iterable $attributes) : self
    {
        $this->attributes = [];
        foreach ($attributes as $k => $v) {
            $this->setAttribute($k, $v);
        }
        return $this;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getTimestamp() : float
    {
        return $this->timestamp;
    }
}