<?php

declare(strict_types=1);

namespace OpenTelemetry\Tracing;

use Exception;

class Span
{
    private $name;
    private $spanContext;
    private $parentSpanContext;

    private $start;
    private $end;
    private $status;

    private $attributes = [];
    private $events = [];

    public function __construct(string $name, SpanContext $spanContext, SpanContext $parentSpanContext = null)
    {
        $this->name = $name;
        $this->spanContext = $spanContext;
        $this->parentSpanContext = $parentSpanContext;
        $this->start = microtime(true);
    }

    public function getSpanContext() : SpanContext
    {
        return $this->spanContext;
    }

    public function getParentSpanContext() : ?SpanContext
    {
        return $this->parentSpanContext;
    }

    public function setParentSpanContext(SpanContext $parentSpanContext = null) : self
    {
        $this->parentSpanContext = $parentSpanContext;
        return $this;
    }

    public function end(Status $status = null) : self
    {
        $this->end = microtime(true);
        if (!$this->status && !$status) {
            $status = new Status(Status::OK);
        }
        if ($status) {
            $this->setStatus($status);
        }
        return $this;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function setStatus(Status $status) : self
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus() : ?Status
    {
        return $this->status;
    }

    public function isRecordingEvents() : bool
    {
        return is_null($this->end);
    }

    public function getDuration() : ?float
    {
        if (!$this->end) {
            return null;
        }
        return $this->end - $this->start;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setInterval(float $start, float $end) : self
    {
        $this->start = $start;
        $this->end = $end;
        return $this;
    }

    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
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
        $this->throwExceptionIfReadonly();

        $this->attributes[$key] = $value;
        return $this;
    }

    public function getAttributes() : array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes) : self
    {
        $this->throwExceptionIfReadonly();

        $this->attributes = [];
        foreach ($attributes as $k => $v) {
            $this->setAttribute($k, $v);
        }
        return $this;
    }

    public function addEvent(string $name, array $attributes = [], float $timestamp = null) : Event
    {
        $this->throwExceptionIfReadonly();

        $event = new Event($name, $attributes, $timestamp);
        $this->events[] = $event;
        return $event;
    }

    public function getEvents()
    {
        return $this->events;
    }

    private function throwExceptionIfReadonly()
    {
        if (!$this->isRecordingEvents()) {
            throw new Exception("Span is readonly");
        }
    }
}