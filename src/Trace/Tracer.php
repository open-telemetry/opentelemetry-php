<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

use OpenTelemetry\Context\SpanContext;

class Tracer
{
    private $active;
    private $spans = [];
    private $tail = [];

    public function __construct(SpanContext $context = null)
    {
        $context = $context ?: SpanContext::generate();
        $this->active = $this->generateSpanInstance('tracer', $context);
    }

    public function getActiveSpan(): Span
    {
        while (count($this->tail) && $this->active->getEnd()) {
            $this->active = array_pop($this->tail);
        }
        return $this->active;
    }

    public function setActiveSpan(Span $span): Span
    {
        $this->tail[] = $this->active;
        return $this->active = $span;
    }

    /* Span creation MUST NOT set the newly created Span as the currently active
     * Span by default, but this functionality MAY be offered additionally as a
     * separate operation.
     * todo: fix ^
     * --> what would you like to do for this?  Would it make sense to return $this->tail[] ?
     * Do we want to offer the setActiveSpan on a newly created span as a separate function?
     *
    */
    /* The API MUST accept the following parameters:
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
     *
     */
    public function createSpan(string $name): Span
    {
        $parent = $this->getActiveSpan()->getContext();
        $context = SpanContext::fork($parent->getTraceId());
        $span = $this->generateSpanInstance($name, $context);
        return $this->setActiveSpan($span);
    }

    public function getSpans(): array
    {
        return $this->spans;
    }

    private function generateSpanInstance($name, SpanContext $context): Span
    {
        $parent = null;
        if ($this->active) {
            $parent = $this->getActiveSpan()->getContext();
        }
        $span = new Span($name, $context, $parent);
        $this->spans[] = $span;
        return $span;
    }
}
