<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use function in_array;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Trace as API;
use OpenTelemetry\Trace\SpanKind;

final class SpanBuilder implements API\SpanBuilder
{
    /**
     * @var non-empty-string
     * @readonly
     */
    private string $spanName;

    /** @readonly */
    private InstrumentationLibrary $instrumentationLibrary;

    /** @readonly */
    private TracerSharedState $tracerSharedState;

    // TODO: Add SpanLimits to constructor

    private ?Context $parentContext = null; // Null means use current context.

    /** @var API\SpanKind::KIND_* */
    private $spanKind = API\SpanKind::KIND_INTERNAL;
    private ?API\Attributes $attributes = null;
    private ?API\Links $links = null;
    private int $totalNumberOfLinksAdded = 0;
    private int $startEpochNanos = 0;

    /** @param non-empty-string $spanName */
    public function __construct(
        string $spanName,
        InstrumentationLibrary $instrumentationLibrary,
        TracerSharedState $tracerSharedState
    ) {
        $this->spanName = $spanName;
        $this->instrumentationLibrary = $instrumentationLibrary;
        $this->tracerSharedState = $tracerSharedState;
    }

    /** @inheritDoc */
    public function setParent(Context $parentContext): API\SpanBuilder
    {
        $this->parentContext = $parentContext;

        return $this;
    }

    /** @inheritDoc */
    public function setNoParent(): API\SpanBuilder
    {
        $this->parentContext = Context::getRoot();

        return $this;
    }

    /** @inheritDoc */
    public function addLink(API\SpanContext $context, API\Attributes $attributes = null): API\SpanBuilder
    {
        if (!$context->isValid()) {
            return $this;
        }

        $this->totalNumberOfLinksAdded++;

        if (null === $this->links) {
            $this->links = new Links();
        }

        // TODO: Skip links over the limit.

        $this->links->addLink(new Link($context, $attributes));

        return $this;
    }

    /** @inheritDoc */
    public function setAttribute(string $key, $value): API\SpanBuilder
    {
        if (null === $this->attributes) {
            $this->attributes = new Attributes();
        }

        $this->attributes->setAttribute($key, $value);

        return $this;
    }

    /** @inheritDoc */
    public function setAttributes(API\Attributes $attributes): API\SpanBuilder
    {
        if (0 === $attributes->count()) {
            return $this;
        }

        foreach ($attributes as $attribute) {
            $this->setAttribute($attribute->getKey(), $attribute->getValue());
        }

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @param SpanKind::KIND_* $spanKind
     */
    public function setSpanKind(int $spanKind): API\SpanBuilder
    {
        $this->spanKind = $spanKind;

        return $this;
    }

    /** @inheritDoc */
    public function setStartTimestamp(int $timestamp): API\SpanBuilder
    {
        if (0 > $timestamp) {
            return $this;
        }

        $this->startEpochNanos = $timestamp;

        return $this;
    }

    /** @inheritDoc */
    public function startSpan(): API\Span
    {
        $parentContext = $this->parentContext ?? Context::getCurrent();
        $parentSpan = Span::fromContext($parentContext);
        $parentSpanContext = $parentSpan->getContext();

        $spanId = $this->tracerSharedState->getIdGenerator()->generateSpanId();

        if (!$parentSpanContext->isValid()) {
            $traceId = $this->tracerSharedState->getIdGenerator()->generateTraceId();
        } else {
            $traceId = $parentSpanContext->getTraceId();
        }

        // Reset links and attributes back to null to prevent mutation of the started span.
        $links = $this->links ?? new Links();
        $this->links = null;

        $attributes = $this->attributes ?? new Attributes();
        $this->attributes = null;

        $samplingResult = $this
            ->tracerSharedState
            ->getSampler()
            ->shouldSample(
                $parentContext,
                $traceId,
                $this->spanName,
                $this->spanKind,
                $attributes,
                $links
            );
        $samplingDecision = $samplingResult->getDecision();
        $samplingResultTraceState = $samplingResult->getTraceState();

        $spanContext = SpanContext::create(
            $traceId,
            $spanId,
            SamplingResult::RECORD_AND_SAMPLE === $samplingDecision ? API\SpanContext::TRACE_FLAG_SAMPLED : API\SpanContext::TRACE_FLAG_DEFAULT,
            $samplingResultTraceState,
        );

        if (!in_array($samplingDecision, [SamplingResult::RECORD_AND_SAMPLE, SamplingResult::RECORD_ONLY], true)) {
            return Span::wrap($spanContext);
        }

        $samplingAttributes = $samplingResult->getAttributes();
        if ($samplingAttributes && $samplingAttributes->count() > 0) {
            foreach ($samplingAttributes as $key => $attribute) {
                $attributes->setAttribute($key, $attribute->getValue());
            }
        }

        return Span::startSpan(
            $this->spanName,
            $spanContext,
            $this->instrumentationLibrary,
            $this->spanKind,
            $parentSpan,
            $parentContext,
            $this->tracerSharedState->getSpanProcessor(),
            $this->tracerSharedState->getResource(),
            $attributes,
            $links,
            $this->totalNumberOfLinksAdded,
            $this->startEpochNanos
        );
    }
}
