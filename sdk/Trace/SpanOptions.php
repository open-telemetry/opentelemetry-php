<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Trace as API;

final class SpanOptions implements API\SpanOptions
{
    private $tracer;
    /**
     * @var string
     */
    private $name;
    private $parent = null;
    private $attributes = null;
    private $links = null;
    private $kind = API\SpanKind::KIND_INTERNAL;

    /** @var int|null */
    private $startEpochTimestamp = null;
    /** @var int|null */
    private $start = null;

    /** @var SpanProcessor|null */
    private $spanProcessor;

    public function __construct(Tracer $tracer, string $name, ?SpanProcessor $spanProcessor = null)
    {
        $this->tracer = $tracer;
        $this->name = $name;
        $this->spanProcessor = $spanProcessor;
    }

    public function setSpanName(string $name): API\SpanOptions
    {
        $this->name = $name;

        return $this;
    }

    public function setSpanKind(int $spanKind): API\SpanOptions
    {
        if (!in_array($spanKind, API\SpanKind::TYPES, true)) {
            throw new \InvalidArgumentException('You must pass a valid span kind');
        }

        $this->kind = $spanKind;

        return $this;
    }

    public function setParent(Context $parentContext): API\SpanOptions
    {
        $parentSpan = Span::extract($parentContext);
        $parentSpanContext = $parentSpan !== null ? $parentSpan->getContext() : SpanContext::getInvalid();
        $this->parent = $parentSpanContext;

        return $this;
    }

    public function addAttributes(API\Attributes $attributes): API\SpanOptions
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function addLinks(API\Links $links): API\SpanOptions
    {
        $this->links = $links;

        return $this;
    }

    public function addStartTimestamp(int $timestamp): API\SpanOptions
    {
        $this->startEpochTimestamp = $timestamp;

        return $this;
    }

    public function addStart(int $now): API\SpanOptions
    {
        $this->start = $now;

        return $this;
    }

    /**
     * @return Span
     */
    public function toSpan(): API\Span
    {
        $span = $this->tracer->getActiveSpan();
        $context = $span->getContext()->isValid()
            ? SpanContext::fork($span->getContext()->getTraceId())
            : SpanContext::fork($this->tracer->getTracerProvider()->getIdGenerator()->generateTraceId());

        $span = new Span($this->name, $context, $this->parent, null, $this->tracer->getResource(), $this->kind, $this->spanProcessor);

        if ($this->startEpochTimestamp !== null) {
            $span->setStartEpochTimestamp($this->startEpochTimestamp);
        }

        if ($this->start !== null) {
            $span->setStart($this->start);
        }

        if (isset($this->attributes)) {
            $span->replaceAttributes($this->attributes);
        }

        if (isset($this->links)) {
            $span->setLinks($this->links);
        }

        return $span;
    }

    /**
     * @return Span
     */
    public function toActiveSpan(): API\Span
    {
        $span = $this->toSpan();
        $this->tracer->setActiveSpan($span);

        return $span;
    }
}
