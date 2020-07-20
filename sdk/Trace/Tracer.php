<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Trace as API;

class Tracer implements API\Tracer
{
    private $active;
    private $spans = [];
    private $tail = [];

    /**
     * @var TracerProvider
     */
    private $provider;

    /**
     * @var ResourceInfo
     */
    private $resource;

    public function __construct(
        TracerProvider $provider,
        ResourceInfo $resource,
        API\SpanContext $context = null
    ) {
        $this->provider = $provider;
        $this->resource = $resource;
        $context = $context ?: SpanContext::generate();

        // todo: hold up, why do we automatically make a root Span?
        $this->active = $this->generateSpanInstance('tracer', $context);
    }

    /**
     * @return Span
     */
    public function getActiveSpan(): API\Span
    {
        while (count($this->tail) && $this->active->getEndTimestamp()) {
            $this->active = array_pop($this->tail);
        }

        return $this->active;
    }

    public function setActiveSpan(API\Span $span): void
    {
        $this->tail[] = $this->active;

        $this->active = $span;
    }

    /* Span creation MUST NOT set the newly created Span as the currently active
     * Span by default, but this functionality MAY be offered additionally as a
     * separate operation.
     * todo: fix ^
     * --> what would you like to do for this?  Would it make sense to return $this->tail[] ?
     * Do we want to offer the setActiveSpan on a newly created span as a separate function?
     *
    */
    /**
     * The API MUST accept the following parameters:
     * - The parent Span or parent Span context, and whether the new Span
     *   should be a root Span. API MAY also have an option for implicit parent
     *   context extraction from the current context as a default behavior.
     * - SpanKind, default to SpanKind.Internal if not specified.
     * - Attributes - similar API with Span::SetAttributes. These attributes
     *   will be used to make a sampling decision as noted in sampling
     *   description. Empty list will be assumed if not specified.
     * - Start timestamp, default to current time. This argument SHOULD only
     *   be set when span creation time has already passed. If API is called
     *   at a moment of a Span logical start, API user MUST not explicitly
     *   set this argument.
     * todo: fix ^
     * -> Is there a reason we didn't add this already?
     * @param string $name
     * @return Span
     */
    public function startAndActivateSpan(string $name): API\Span
    {
        $parent = $this->getActiveSpan()->getContext();
        $context = SpanContext::fork($parent->getTraceId(), $parent->isSampled());
        $span = $this->generateSpanInstance($name, $context);

        if ($span->isRecording()) {
            $this->provider->getSpanProcessor()->onStart($span);
        }

        $this->setActiveSpan($span);

        return $this->active;
    }

    public function getSpans(): array
    {
        return $this->spans;
    }

    public function endActiveSpan(?int $timestamp = null)
    {
        /**
         * a span should be ended before is sent to exporters, because the exporters need's span duration.
         */
        $span = $this->getActiveSpan();
        $wasRecording = $span->isRecording();
        $span->end();

        if ($wasRecording) {
            $this->provider->getSpanProcessor()->onEnd($span);
        }
    }

    private function generateSpanInstance($name, API\SpanContext $context): Span
    {
        $parent = null;
        if ($this->active) {
            $parent = $this->getActiveSpan()->getContext();
        }
        $span = new Span($name, $context, $parent);
        $this->spans[] = $span;

        return $span;
    }

    public function startSpanWithOptions(string $name): API\SpanOptions
    {
        return new SpanOptions($this, $name);
    }

    public function finishSpan(API\Span $span, ?int $timestmap = null): void
    {
        // TODO: Implement finishSpan() method.
    }

    public function deactivateActiveSpan(): void
    {
        // TODO: Implement deactivateActiveSpan() method.
    }
}
