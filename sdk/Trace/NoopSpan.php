<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

class NoopSpan implements \OpenTelemetry\Trace\Span
{
    /** @var SpanContext */
    private $context;

    /** @var API\Attributes */
    private $attributes;

    /** @var API\Links */
    private $links;
    // @todo when links will be implemented, this attribute should be initialized properly

    /** @var API\Events */
    private $events;

    /** @var API\SpanStatus */
    private $status;

    /*
     * All operations are no-op except context propagation.
     * If a valid context is passed in, the context is propagated;
     * otherwise, it returns an invalid span.
     */
    public function __construct(SpanContext $spanContext = null)
    {
        if (null == $spanContext) {
            $this->context = new SpanContext(
                SpanContext::INVALID_TRACE,
                SpanContext::INVALID_SPAN,
                0,
                []
            );
        } else {
            $this->context = $spanContext;
        }
        $this->attributes = new Attributes();
        $this->events = new Events();
        $this->status = SpanStatus::ok();
    }

    public function getSpanName(): string
    {
        return '';
    }

    public function getContext(): API\SpanContext
    {
        return $this->context;
    }

    public function getParent(): ?API\SpanContext
    {
        return null;
    }

    public function getStartEpochTimestamp(): int
    {
        return 0;
    }

    public function getStart(): int
    {
        return 0;
    }

    public function getEnd(): ?int
    {
        return 0;
    }

    public function getAttributes(): API\Attributes
    {
        return $this->attributes;
    }

    public function getLinks(): API\Links
    {
        return $this->links;
    }

    public function getEvents(): API\Events
    {
        return $this->events;
    }

    public function getStatus(): API\SpanStatus
    {
        return $this->status;
    }

    public function setAttribute(string $key, $value): API\Span
    {
        return $this;
    }

    public function addEvent(string $name, int $timestamp, ?API\Attributes $attributes = null): API\Span
    {
        return $this;
    }

    public function addLink(API\SpanContext $context, ?API\Attributes $attributes = null): API\Span
    {
        return $this;
    }

    public function updateName(string $name): API\Span
    {
        return $this;
    }

    public function setSpanStatus(string $code, ?string $description = null): API\Span
    {
        return $this;
    }

    public function end(int $timestamp = null): API\Span
    {
        return $this;
    }

    public function isRecording(): bool
    {
        return false;
    }

    public function isSampled(): bool
    {
        return false;
    }

    public function getSpanKind(): int
    {
        return API\SpanKind::KIND_INTERNAL;
    }

    public function getCanonicalStatusCode(): string
    {
        return $this->status->getCanonicalStatusCode();
    }

    public function getStatusDescription(): string
    {
        return $this->status->getStatusDescription();
    }

    public function isStatusOk(): bool
    {
        return $this->status->isStatusOK();
    }
}
