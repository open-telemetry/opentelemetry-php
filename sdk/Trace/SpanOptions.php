<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

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

    public function __construct(Tracer $tracer, string $name)
    {
        $this->tracer = $tracer;
        $this->name = $name;
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

    public function setParentContext(API\SpanContext $span): API\SpanOptions
    {
        $this->parent = $span;

        return $this;
    }

    public function setParentSpan(API\Span $span): API\SpanOptions
    {
        $this->parent = $span->getContext();

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

    public function toSpan(): API\Span
    {
        $span = $this->tracer->getActiveSpan();
        $context = $span->getContext()->isValid()
            ? SpanContext::fork($span->getContext()->getTraceId())
            : SpanContext::generate();

        $span = new Span($this->name, $context, $this->parent, null, $this->tracer->getResource(), $this->kind);

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

    public function toActiveSpan(): API\Span
    {
        $span = $this->toSpan();
        $this->tracer->setActiveSpan($span);

        return $span;
    }
}
