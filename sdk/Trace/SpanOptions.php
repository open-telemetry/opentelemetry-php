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

    /** @var string|null */
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
        // TODO: Implement setSpanKind() method.
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

    public function addStartTimestamp($timestamp): API\SpanOptions
    {
        $this->start = $timestamp;

        return $this;
    }

    public function toSpan(): API\Span
    {
        $span = $this->tracer->getActiveSpan();
        $context = $span->getContext()->IsValidContext()
            ? SpanContext::fork($span->getContext()->getTraceId())
            : SpanContext::generate();

        $span = new Span($this->name, $context, $this->parent);

        if (isset($this->start)) {
            $span->setStartTimestamp($this->start);
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
