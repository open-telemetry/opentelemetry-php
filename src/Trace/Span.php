<?php
declare(strict_types=1);

namespace OpenTelemetry\Trace;

use Exception;
use OpenTelemetry\Context\SpanContext;

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
    private $link = [];

    // todo: missing span kind
    // https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/api-tracing.md#spankind

    // todo: missing links: https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/api-tracing.md#add-links

    // -> Need to understand the difference between SpanKind and links.  From the documentation:
    // SpanKind
    // describes the relationship between the Span, its parents, and its children in a Trace. SpanKind describes two independent properties that benefit tracing systems during analysis.
    // This was also updated recently -> https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/api-tracing.md#spankind

    // Links
    // A Span may be linked to zero or more other Spans (defined by SpanContext) that are causally related. Links can point to SpanContexts inside a single Trace
    // or across different Traces. Links can be used to represent batched operations where a Span was initiated by multiple initiating Spans,
    // each representing a single incoming item being processed in the batch.
    // https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/overview.md#links-between-spans

    public function __construct(string $name, SpanContext $spanContext, SpanContext $parentSpanContext = null)
    {
        $this->name = $name;
        $this->spanContext = $spanContext;
        $this->parentSpanContext = $parentSpanContext;
        $this->start = microtime(true);
        $this->statusCode = Status::OK;
        $this->statusDescription = null;
        $this->link = $this->addLinks();
    }

    public function getContext(): SpanContext
    {
        return clone $this->spanContext;
    }

    public function getParentContext(): ?SpanContext
    {
        // todo: Spec says a parent is a Span, SpanContext, or null -> should we implement this here?
        return $this->parentSpanContext !== null ? clone $this->parentSpanContext : null;
    }

    public function addLinks(): array
    {
        return [];
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

    public function getStatus(): Status
    {
        return Status::new($this->statusCode, $this->statusDescription);
    }

    // I think this is too simple, see: https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/api-tracing.md#isrecording
    // -> This had an update this past month: https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/api-tracing.md#isrecording
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
        if (!is_string($value) || !is_bool($value) || !is_int($value)) {
            $this->throwIfNotRecording();
        }

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

    // todo: is accepting an Iterator enough to satisfy AddLazyEvent?  -> Looks like the spec might have been updated here: https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/api-tracing.md#add-events
    public function addEvent(string $name, iterable $attributes = [], float $timestamp = null): Event
    {
        $this->throwIfNotRecording();

        $event = new Event($name, $attributes, $timestamp);
        // todo: check that these are all Attributes -> What do we want to check about these?  Just a 'property_exist' check on this?
        $this->events[] = $event;
        return $event;
    }

    public function getEvents()
    {
        return $this->events;
    }

    /* A Span is said to have a remote parent if it is the child of a Span
     * created in another process. Each propagators' deserialization must set IsRemote to true on a parent
     *  SpanContext so Span creation knows if the parent is remote.
     * TODO - finish this function
    */
    public function IsRemote(): bool
    {
        return false;
    }

    private function throwIfNotRecording()
    {
        if (!$this->isRecording()) {
            throw new Exception("Span is readonly");
        }
    }
}