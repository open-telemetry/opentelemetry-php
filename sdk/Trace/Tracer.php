<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

class Tracer implements API\Tracer
{
    private $active;
    private $spans = [];
    private $tail = [];
    /**
     * @var SpanProcessor[]
     */
    private $spanProcessors = [];

    /**
     * Tracer constructor.
     *
     * @param SpanProcessor[]         $spanProcessors
     * @param API\SpanContext|null                 $context
     */
    public function __construct(iterable $spanProcessors = [], API\SpanContext $context = null)
    {
        $context = $context ?: SpanContext::generate();

        // todo: hold up, why do we automatically make a root Span?
        $this->active = $this->generateSpanInstance('tracer', $context);
        $this->spanProcessors = $spanProcessors;
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
        $context = SpanContext::fork($parent->getTraceId());
        $span = $this->generateSpanInstance($name, $context);

        if ($span->isRecording()) {
            foreach ($this->spanProcessors as $spanProcessor) {
                $spanProcessor->onStart($span);
            }
        }

        $this->setActiveSpan($span);

        return $this->active;
    }

    public function getSpans(): array
    {
        return $this->spans;
    }

    public function endActiveSpan(?string $timestamp = null)
    {
        // todo: should processors be called before or after end()?
        if ($this->getActiveSpan()->isRecording()) {
            foreach ($this->spanProcessors as $spanProcessor) {
                $spanProcessor->onEnd($this->getActiveSpan());
            }
        }

        $this->getActiveSpan()->end($timestamp);
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

    public function finishSpan(API\Span $span, ?string $timestamp = null): void
    {
        // TODO: Implement finishSpan() method.
    }

    public function deactivateActiveSpan(): void
    {
        // TODO: Implement deactivateActiveSpan() method.
    }
}
