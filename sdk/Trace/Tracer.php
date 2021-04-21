<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Trace as API;

class Tracer implements API\Tracer
{
    private $active;
    private $spans = [];
    private $tail = [];

    /** @var TracerProvider  */
    private $provider;
    /** @var ResourceInfo */
    private $resource;
    /** @var API\SpanContext|null  */
    private $importedContext;

    public function __construct(
        TracerProvider $provider,
        ResourceInfo $resource = null,
        API\SpanContext $context = null
    ) {
        $this->provider = $provider;
        $this->resource = $resource ?? ResourceInfo::emptyResource();
        $this->importedContext = $context;
    }

    public function startSpan(
        string $name,
        ?Context $parentContext = null,
        int $spanKind = API\SpanKind::KIND_INTERNAL,
        ?API\Attributes $attributes = null,
        ?API\Links $links = null,
        ?int $startTimestamp = null
    ): API\Span {
        $parentSpan = $parentContext !== null ? Span::extract($parentContext) : Span::getCurrent();
        $parentSpanContext = $parentSpan !== null ? $parentSpan->getContext() : SpanContext::getInvalid();

        /**
         * Implementations MUST generate a new TraceId for each root span created.
         * For a Span with a parent, the TraceId MUST be the same as the parent.
         */
        $traceId = $parentSpanContext->isValid() ? $parentSpanContext->getTraceId() : $this->provider->getIdGenerator()->generateTraceId();
        $spanId = $this->provider->getIdGenerator()->generateSpanId();

        $sampleResult = $this->provider->getSampler()->shouldSample(
            $parentContext ?? Context::getCurrent(),
            $traceId,
            $name,
            $spanKind,
            $attributes,
            $links
        );

        $attributes = $attributes ?? new Attributes();
        $sampleAttributes = $sampleResult->getAttributes();
        if ($sampleAttributes !== null) {
            foreach ($sampleAttributes as $name => $value) {
                $attributes->setAttribute($name, $value);
            }
        }

        $traceFlags = $sampleResult->getDecision() === SamplingResult::RECORD_AND_SAMPLE ? SpanContext::TRACE_FLAG_SAMPLED : 0;
        $traceState = $sampleResult->getTraceState();
        $spanContext = new SpanContext($traceId, $spanId, $traceFlags, $traceState);

        if ($sampleResult->getDecision() === SamplingResult::DROP) {
            return new NoopSpan($spanContext);
        }

        $span = new Span(
            $name,
            $spanContext,
            $parentSpanContext,
            $this->provider->getSampler(),
            $this->resource,
            $spanKind,
            $this->provider->getSpanProcessor()
        );

        $this->provider->getSpanProcessor()->onStart($span, $parentContext ?? Context::getCurrent());

        return $span;
    }

    public function getActiveSpan(): API\Span
    {
        while (count($this->tail) && ($this->active->ended())) {
            $this->active = array_pop($this->tail);
        }

        return $this->active ?? new NoopSpan();
    }

    public function setActiveSpan(API\Span $span): void
    {
        $this->tail[] = $this->active;

        $this->active = $span;
    }

    /**
     * @param string $name
     * @param API\SpanContext $parentContext
     * @param bool $isRemote
     * @param int $spanKind
     * @return Span
     */

    public function startActiveSpan(string $name, API\SpanContext $parentContext, bool $isRemote = false, int $spanKind = API\SpanKind::KIND_INTERNAL): API\Span
    {
        $parentContextIsNoopSpan = !$parentContext->isValid();

        if ($parentContextIsNoopSpan) {
            $parentContext = $this->importedContext ?? SpanContext::generate(true);
        }

        /*
         * The sampler returns a sampling SamplingResult based on information that is typically
         * available just before the Span was created.
         * Based on this, it decides whether to create a real or Noop (non-recorded/non-exported) span.
         */
        // When attributes and links are coded, they will need to be passed in here.
        $sampler = $this->provider->getSampler();
        $samplingResult = $sampler->shouldSample(
            Span::insert(new NoopSpan($parentContext), new Context()),
            $parentContext->getTraceId(),
            $name,
            $spanKind
        );

        $context = SpanContext::fork($parentContext->getTraceId(), $parentContext->isSampled(), $isRemote);

        if (SamplingResult::DROP == $samplingResult->getDecision()) {
            $span = $this->generateSpanInstance('', $context);
        } else {
            $span = $this->generateSpanInstance($name, $context, $parentContext, $sampler, $this->resource, $spanKind);

            if ($span->isRecording()) {
                $this->provider->getSpanProcessor()->onStart($span);
            }
        }

        $this->setActiveSpan($span);

        return $this->active;
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
     * @param API\SpanContext $parentContext
     * @param bool $isRemote
     * @return Span
     */
    public function startAndActivateSpanFromContext(string $name, API\SpanContext $parentContext, bool $isRemote = false, int $spanKind = API\SpanKind::KIND_INTERNAL): API\Span
    {
        /*
         * Pass in true if the SpanContext was propagated from a
         * remote parent. When creating children from remote spans,
         * their IsRemote flag MUST be set to false.
         */
        return self::startActiveSpan($name, $parentContext, $isRemote, $spanKind);
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
    public function startAndActivateSpan(string $name, int $spanKind = API\SpanKind::KIND_INTERNAL): API\Span
    {
        $parentContext = $this->getActiveSpan()->getContext();

        return self::startActiveSpan($name, $parentContext, false, $spanKind);
    }

    public function getSpans(): array
    {
        return $this->spans;
    }
    public function getResource(): ResourceInfo
    {
        return clone $this->resource;
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

    private function generateSpanInstance(string $name, API\SpanContext $context, API\SpanContext $parentContext = null, Sampler $sampler = null, ResourceInfo $resource = null, int $spanKind = API\SpanKind::KIND_INTERNAL): API\Span
    {
        $parent = null;

        if (null == $sampler) {
            $span = new NoopSpan($context);
        } else {
            if ($this->active) {
                $parent = $this->getActiveSpan()->getContext();
            } elseif (is_object($parentContext) && $parentContext->isRemote() == true) {
                $parent = $parentContext;
            }

            $span = new Span($name, $context, $parent, $sampler, $resource, $spanKind);
        }
        $this->spans[] = $span;

        return $span;
    }

    public function startSpanWithOptions(string $name): API\SpanOptions
    {
        return new SpanOptions($this, $name);
    }

    public function finishSpan(API\Span $span, ?int $timestamp = null): void
    {
        // TODO: Implement finishSpan() method.
    }

    public function deactivateActiveSpan(): void
    {
        // TODO: Implement deactivateActiveSpan() method.
    }
}
