<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Trace as API;

class Tracer implements API\Tracer
{
    private $active;
    private $spans = [];
    private $tail = [];

    /** @var TracerProvider */
    private $provider;

    /** @var ResourceInfo */
    private $resource;

    /** @var InstrumentationLibrary */
    private $instrumentationLibrary;

    /** @var API\SpanContext|null */
    private $importedContext;

    public function __construct(
        TracerProvider $provider,
        InstrumentationLibrary $instrumentationLibrary,
        ResourceInfo $resource = null,
        API\SpanContext $context = null
    ) {
        $this->provider = $provider;
        $this->instrumentationLibrary = $instrumentationLibrary;
        $this->resource = $resource ?? ResourceInfo::emptyResource();
        $this->importedContext = $context;
    }

    /**
     * @return Span|NonRecordingSpan
     */
    public function startSpan(
        string $name,
        ?Context $parentContext = null,
        int $spanKind = API\SpanKind::KIND_INTERNAL,
        ?API\Attributes $attributes = null,
        ?API\Links $links = null,
        int $startTimestamp = 0
    ): API\Span {
        $parentContext = $parentContext ?? Context::getCurrent();
        $parentSpan = Span::fromContext($parentContext);
        $parentSpanContext = $parentSpan->getContext();

        $spanId = $this->provider->getIdGenerator()->generateSpanId();

        if (!$parentSpanContext->isValid()) {
            $traceId = $this->provider->getIdGenerator()->generateTraceId();
        } else {
            $traceId = $parentSpanContext->getTraceId();
        }

        $sampleResult = $this->provider->getSampler()->shouldSample(
            $parentContext,
            $traceId,
            $name,
            $spanKind,
            $attributes,
            $links
        );

        $attributes = $attributes ?? new Attributes();
        $sampleAttributes = $sampleResult->getAttributes();
        if ($sampleAttributes !== null) {
            foreach ($sampleAttributes as $attrName => $value) {
                $attributes->setAttribute($attrName, $value->getValue());
            }
        }

        $traceFlags = $sampleResult->getDecision() === SamplingResult::RECORD_AND_SAMPLE ? API\SpanContext::TRACE_FLAG_SAMPLED : 0;
        $traceState = $sampleResult->getTraceState();
        $spanContext = new SpanContext($traceId, $spanId, $traceFlags, $traceState);

        if ($sampleResult->getDecision() === SamplingResult::DROP) {
            return NonRecordingSpan::create($spanContext);
        }

        $links = $links ?? new Links();

        return Span::startSpan(
            $name,
            $spanContext,
            $this->instrumentationLibrary,
            $spanKind,
            $parentSpan,
            $parentContext,
            $this->provider->getSpanProcessor(),
            $this->resource,
            $attributes,
            $links,
            $links->count(), // TODO: Is this sufficient?
            $startTimestamp
        );
    }

    /**
     * @return Span|NonRecordingSpan
     */
    public function getActiveSpan(): API\Span
    {
        while (count($this->tail) && ($this->active->ended())) {
            $this->active = array_pop($this->tail);
        }

        return $this->active ?? NonRecordingSpan::create(SpanContext::getInvalid());
    }

    public function setActiveSpan(API\Span $span): void
    {
        $this->tail[] = $this->active;

        $this->active = $span;

        // FIXME: This should either be called manually or as part of a dedicated tracer operation,
        // probably some sort of `inSpan` method?
        $span->activate();
    }

    /**
     * @psalm-return Span
     * @return Span|NonRecordingSpan
     */
    public function startActiveSpan(
        string $name,
        API\SpanContext $parentContext,
        bool $isRemote = false,
        int $spanKind = API\SpanKind::KIND_INTERNAL,
        ?API\Attributes $attributes = null,
        ?API\Links $links = null
    ): API\Span {
        $parentContextIsNoopSpan = !$parentContext->isValid();

        if ($parentContextIsNoopSpan) {
            $parentContext = $this->importedContext ?? SpanContext::fork($this->provider->getIdGenerator()->generateTraceId(), true);
        }

        /*
         * The sampler returns a sampling SamplingResult based on information that is typically
         * available just before the Span was created.
         * Based on this, it decides whether to create a real or Noop (non-recorded/non-exported) span.
         */
        // When attributes and links are coded, they will need to be passed in here.
        $sampler = $this->provider->getSampler();
        $samplingResult = $sampler->shouldSample(
            (new Context())->withContextValue(NonRecordingSpan::create($parentContext)),
            $parentContext->getTraceId(),
            $name,
            $spanKind,
            $attributes,
            $links
        );

        $context = SpanContext::fork($parentContext->getTraceId(), $parentContext->isSampled(), $isRemote);

        if (SamplingResult::DROP === $samplingResult->getDecision()) {
            $span = $this->generateSpanInstance('', $context);
        } else {
            $span = $this->generateSpanInstance(
                $name,
                $context,
                $parentContext,
                $sampler,
                $this->resource,
                $spanKind,
                $samplingResult->getAttributes(),
                $links
            );

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
     * @psalm-return Span
     * @return Span|NonRecordingSpan
     */
    public function startAndActivateSpanFromContext(
        string $name,
        API\SpanContext $parentContext,
        bool $isRemote = false,
        int $spanKind = API\SpanKind::KIND_INTERNAL,
        ?API\Attributes $attributes = null,
        ?API\Links $links = null
    ): API\Span {
        /*
         * Pass in true if the SpanContext was propagated from a
         * remote parent. When creating children from remote spans,
         * their IsRemote flag MUST be set to false.
         */
        return $this->startActiveSpan($name, $parentContext, $isRemote, $spanKind, $attributes, $links);
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
     * @psalm-return Span
     * @return Span|NonRecordingSpan
     */
    public function startAndActivateSpan(
        string $name,
        int $spanKind = API\SpanKind::KIND_INTERNAL,
        ?API\Attributes $attributes = null,
        ?API\Links $links = null
    ): API\Span {
        $parentContext = $this->getActiveSpan()->getContext();

        return $this->startActiveSpan($name, $parentContext, false, $spanKind, $attributes, $links);
    }

    public function getSpans(): array
    {
        return $this->spans;
    }

    public function getResource(): ResourceInfo
    {
        return clone $this->resource;
    }

    public function getTracerProvider(): TracerProvider
    {
        return $this->provider;
    }

    public function startSpanWithOptions(string $name): API\SpanOptions
    {
        return new SpanOptions($this, $name, $this->provider->getSpanProcessor());
    }

    /**
     * @param non-empty-string $name
     * @param API\SpanKind::KIND_* $spanKind
     */
    private function generateSpanInstance(
        string $name,
        API\SpanContext $context,
        API\SpanContext $parentContext = null,
        Sampler $sampler = null,
        ResourceInfo $resource = null,
        int $spanKind = API\SpanKind::KIND_INTERNAL,
        ?API\Attributes $attributes = null,
        ?API\Links $links = null
    ): API\Span {
        if (null === $sampler) {
            $span = NonRecordingSpan::create($context);
        } else {
            $links = $links ?? new Links();

            $span = Span::startSpan(
                $name,
                $context,
                $this->instrumentationLibrary,
                $spanKind,
                Span::fromContext(Context::getCurrent()),
                Context::getCurrent(),
                $this->provider->getSpanProcessor(),
                $resource ?? ResourceInfo::emptyResource(),
                $attributes,
                $links,
                $links->count(), // TODO: Is this sufficient?
                Clock::get()->timestamp()
            );
        }

        $this->spans[] = $span;

        return $span;
    }
}
