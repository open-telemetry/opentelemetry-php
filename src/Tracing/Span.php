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
    private $statusCode;
    private $statusDescription;

    private $attributes = [];
    private $events = [];

    // todo: missing span kind

    // todo: missing links: https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/api-tracing.md#add-links

    // todo: Spec says a parent is a Span, SpanContext, or null

    public function __construct(string $name, SpanContext $spanContext, SpanContext $parentSpanContext = null)
    {
        $this->name = $name;
        $this->spanContext = $spanContext;
        $this->parentSpanContext = $parentSpanContext;
        $this->start = microtime(true);
        $this->status = Status::OK;
        $this->statusDescription = null;
    }

    public function getContext(): SpanContext
    {
        return clone $this->spanContext;
    }

    public function getParentContext(): ?SpanContext
    {
        return $this->parentSpanContext !== null ? clone $this->parentSpanContext : null;
    }


    public function end(int $statusCode = Status::OK, ?string $statusDescription = null, float $timestamp = null): self
    {
        $this->end = $timestamp ?? microtime(true);
        $this->statusCode = $statusCode;
        $this->statusDescription = null;
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

    // todo: we decided setStatus did not seem necessary, and complicates the hotpath, and therefore not worth it
    //public function setStatus(Status $status);

    public function getStatus(): Status
    {
        return Status::new($this->statusCode, $this->statusDescription);
    }

    // I think this is too simple, see: https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/api-tracing.md#isrecording
    public function isRecording(): bool
    {
        return is_null($this->end);
    }

    public function getDuration(): ?float
    {
        if (!$this->end) {
            return null;
        }
        return $this->end - $this->start;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function updateName(string $name): self
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

    public function setAttribute(string $key, $value): self
    {
        // todo: type check value:
        // https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/api-tracing.md#set-attributes
        $this->throwIfNotRecording();

        $this->attributes[$key] = $value;
        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(iterable $attributes): self
    {
        $this->throwIfNotRecording();

        $this->attributes = [];
        foreach ($attributes as $k => $v) {
            $this->setAttribute($k, $v);
        }
        return $this;
    }

    // todo: is accepting an Iterator enough to satisfy AddLazyEvent?
    public function addEvent(string $name, iterable $attributes = [], float $timestamp = null): Event
    {
        $this->throwIfNotRecording();

        $event = new Event($name, $attributes, $timestamp);
        // todo: check that these are all Attributes
        $this->events[] = $event;
        return $event;
    }

    public function getEvents()
    {
        return $this->events;
    }

    /* A Span is said to have a remote parent if it is the child of a Span
     * created in another process. Each propagators' deserialization must set IsRemote to true on a parent
     *  SpanContext so Span creation knows if the parent is remote. */
    public function IsRemote(): bool
    {
        ;
    }

    private function throwIfNotRecording()
    {
        if (!$this->isRecording()) {
            throw new Exception("Span is readonly");
        }
    }
}