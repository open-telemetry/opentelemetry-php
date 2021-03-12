<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use Exception;
use OpenTelemetry\Context\ContextKey;
use OpenTelemetry\Context\ContextValueTrait;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Trace as API;

class Span implements API\Span
{
    use ContextValueTrait;

    private $name;
    private $spanContext;
    private $parentSpanContext;
    private $spanKind;
    private $sampler;

    private $startEpochTimestamp;
    private $start;
    private $end;

    private $spanStatus;

    /**
     * @var ResourceInfo
     */
    private $resource; // An immutable representation of the entity producing telemetry.

    private $attributes;
    private $events;
    private $links = null;

    private $ended = false;

    /** @var ?SpanProcessor */
    private $spanProcessor;

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
        ?Sampler $sampler = null,
        ?ResourceInfo $resource = null,
        int $spanKind = API\SpanKind::KIND_INTERNAL,
        ?SpanProcessor $spanProcessor = null
    ) {
        $this->name = $name;
        $this->spanContext = $spanContext;
        $this->parentSpanContext = $parentSpanContext;
        $this->spanKind = $spanKind;
        $this->sampler = $sampler;
        $this->resource =  $resource ?? ResourceInfo::emptyResource();
        $this->spanProcessor = $spanProcessor;
        $moment = Clock::get()->moment();
        $this->startEpochTimestamp = $moment[0];
        $this->start = $moment[1];
        $this->spanStatus = new SpanStatus();

        // todo: set these to null until needed
        $this->attributes = new Attributes();
        $this->events = new Events();
    }

    public function getResource(): ResourceInfo
    {
        return clone $this->resource;
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

    public function setSpanStatus(string $code, ?string $description = null): API\Span
    {
        if ($this->isRecording()) {
            $this->spanStatus->setStatus($code, $description);
        }

        return $this;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function setStart(int $start): Span
    {
        $this->start = $start;

        return $this;
    }

    public function end(?int $now = null): API\Span
    {
        if (!isset($this->end)) {
            $this->end = $now ?? Clock::get()->now();
            $this->ended = true;
        }

        if ($this->spanProcessor !== null) {
            $this->spanProcessor->onEnd($this);
        }

        return $this;
    }
    public function ended(): bool
    {
        return $this->ended;
    }
    public function setStartEpochTimestamp(int $timestamp): Span
    {
        $this->startEpochTimestamp = $timestamp;

        return $this;
    }

    public function getStartEpochTimestamp(): int
    {
        return $this->startEpochTimestamp;
    }

    public function getEnd(): ?int
    {
        return $this->end;
    }

    public function getStatus(): API\SpanStatus
    {
        return $this->spanStatus;
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

    public function replaceAttributes(API\Attributes $attributes): self
    {
        if ($this->isRecording()) {
            $this->attributes = $attributes;
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

    public function recordException(Exception $exception): API\Span
    {
        $attributes = new Attributes(
            [
                'exception.type' => get_class($exception),
                'exception.message' => $exception->getMessage(),
                'exception.stacktrace' => $exception->getTraceAsString(),
            ]
        );
        $timestamp = time();

        return  $this->addEvent('exception', $timestamp, $attributes);
    }

    public function getEvents(): API\Events
    {
        return $this->events;
    }

    /* A Span is said to have a remote parent if it is the child of a Span
     * created in another process. Each propagators' deserialization must
     * set IsRemote to true on a parent
     * SpanContext so Span creation knows if the parent is remote.
     * Returns true if the SpanContext was propagated from a
     * remote parent. When creating children
     * from remote spans, their IsRemote flag MUST be set to false.
    */
    public function isRemote(): bool
    {
        return $this->spanContext->isRemote();
    }

    public function isSampled(): bool
    {
        return $this->spanContext->isSampled();
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

    public function getCanonicalStatusCode(): string
    {
        return $this->spanStatus->getCanonicalStatusCode();
    }

    public function getStatusDescription(): string
    {
        return $this->spanStatus->getStatusDescription();
    }

    public function isStatusOk(): bool
    {
        return $this->spanStatus->isStatusOK();
    }

    /**
     * @return ContextKey
     * @phan-override
     */
    protected static function getContextKey(): ContextKey
    {
        return SpanContextKey::instance();
    }
}
