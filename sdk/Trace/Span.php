<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

class Span implements API\Span
{
    private $name;
    private $spanContext;
    private $parentSpanContext;
    private $spanKind;

    private $start;
    private $end;
    private $statusCode = API\SpanStatus::OK;

    /** @var string  */
    private $statusDescription = API\SpanStatus::DESCRIPTION[API\SpanStatus::UNKNOWN];

    private $attributes;
    private $events;
    private $links = null;

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

    public function __construct(
        string $name,
        API\SpanContext $spanContext,
        ?API\SpanContext $parentSpanContext = null,
        int $spanKind = API\SpanKind::KIND_INTERNAL
    ) {
        $this->name = $name;
        $this->spanContext = $spanContext;
        $this->parentSpanContext = $parentSpanContext;
        $this->spanKind = $spanKind;
        $this->start = Clock::get()->timestamp();
        $this->statusCode = API\SpanStatus::OK;
        $this->statusDescription = API\SpanStatus::DESCRIPTION[$this->statusCode];

        // todo: set these to null until needed
        $this->attributes = new Attributes();
        $this->events = new Events();
    }

    public function getContext(): API\SpanContext
    {
        return clone $this->spanContext;
    }

    public function getParent(): ?API\SpanContext
    {
        // todo: Spec says a parent is a Span, SpanContext, or null -> should we implement this here?
        return $this->parentSpanContext !== null ? clone $this->parentSpanContext : null;
    }

    public function setSpanStatus(int $code, ?string $description = null): API\Span
    {
        if ($this->isRecording()) {
            $this->statusCode = $code;
            $this->statusDescription = $description ?? self::DESCRIPTION[$code] ?? self::DESCRIPTION[self::UNKNOWN];
        }

        return $this;
    }

    public function end(int $timestamp = null): API\Span
    {
        if (!isset($this->end)) {
            $this->end = $timestamp ?? Clock::get()->timestamp();
        }

        return $this;
    }

    public function setStartTimestamp(int $timestamp): Span
    {
        $this->start = $timestamp;

        return $this;
    }

    public function getStartTimestamp(): int
    {
        return $this->start;
    }

    public function getEndTimestamp(): ?int
    {
        return $this->end;
    }

    public function getStatus(): API\SpanStatus
    {
        return SpanStatus::new($this->statusCode, $this->statusDescription);
    }

    public function isRecording(): bool
    {
        return null === $this->end;
    }

    public function getDuration(): ?int
    {
        if (!$this->end) {
            return null;
        }

        return ($this->end - $this->start);
    }

    public function getSpanName(): string
    {
        return $this->name;
    }

    public function updateName(string $name): API\Span
    {
        $this->name = $name;

        return $this;
    }

    public function getAttribute(string $key): ?Attribute
    {
        return $this->attributes->getAttribute($key);
    }

    public function setAttribute(string $key, $value): API\Span
    {
        if ($this->isRecording()) {
            $this->attributes->setAttribute($key, $value);
        }

        return $this;
    }

    public function getAttributes(): API\Attributes
    {
        return $this->attributes;
    }

    public function replaceAttributes(iterable $attributes): self
    {
        if ($this->isRecording()) {
            $this->attributes = new Attributes();
            foreach ($attributes as $k => $v) {
                $this->setAttribute($k, $v);
            }
        }

        return $this;
    }

    // todo: is accepting an Iterator enough to satisfy AddLazyEvent?  -> Looks like the spec might have been updated here: https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/api-tracing.md#add-events
    public function addEvent(string $name, int $timestamp, ?API\Attributes $attributes = null): API\Span
    {
        if ($this->isRecording()) {
            $this->events->addEvent($name, $attributes, $timestamp);
        }

        return $this;
    }

    public function getEvents(): API\Events
    {
        return $this->events;
    }

    /* A Span is said to have a remote parent if it is the child of a Span
     * created in another process. Each propagators' deserialization must set IsRemote to true on a parent
     *  SpanContext so Span creation knows if the parent is remote.
     * TODO - finish this function
    */
    public function isRemote(): bool
    {
        return false;
    }

    public function setLinks(API\Links $links): Span
    {
        $this->links = $links;

        return $this;
    }

    public function getLinks(): API\Links
    {
        // TODO: Implement getLinks() method.
        return $this->links;
    }

    /**
     * @inheritDoc
     */
    public function addLink(API\SpanContext $context, ?API\Attributes $attributes = null): API\Span
    {
        return $this;
    }

    public function getSpanKind(): int
    {
        return $this->spanKind;
    }

    public function getCanonicalStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getStatusDescription(): string
    {
        return $this->statusDescription;
    }

    public function isStatusOk(): bool
    {
        return $this->statusCode == API\SpanStatus::OK;
    }
}
