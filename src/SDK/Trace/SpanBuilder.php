<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function in_array;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\AttributeLimits;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\AttributesInterface;
use OpenTelemetry\SDK\InstrumentationLibrary;

final class SpanBuilder implements API\SpanBuilderInterface
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

    /** @readonly */
    private SpanLimits $spanLimits;

    private ?Context $parentContext = null; // Null means use current context.

    /**
     * @psalm-var API\SpanKind::KIND_*
     */
    private int $spanKind = API\SpanKind::KIND_INTERNAL;

    /** @var list<LinkInterface>|null */
    private ?array $links = null;

    private ?AttributesInterface $attributes = null;
    private int $totalNumberOfLinksAdded = 0;
    private int $startEpochNanos = 0;

    /** @param non-empty-string $spanName */
    public function __construct(
        string $spanName,
        InstrumentationLibrary $instrumentationLibrary,
        TracerSharedState $tracerSharedState,
        SpanLimits $spanLimits
    ) {
        $this->spanName = $spanName;
        $this->instrumentationLibrary = $instrumentationLibrary;
        $this->tracerSharedState = $tracerSharedState;
        $this->spanLimits = $spanLimits;
    }

    /** @inheritDoc */
    public function setParent(Context $parentContext): API\SpanBuilderInterface
    {
        $this->parentContext = $parentContext;

        return $this;
    }

    /** @inheritDoc */
    public function setNoParent(): API\SpanBuilderInterface
    {
        $this->parentContext = Context::getRoot();

        return $this;
    }

    /** @inheritDoc */
    public function addLink(API\SpanContextInterface $context, iterable $attributes = []): API\SpanBuilderInterface
    {
        if (!$context->isValid()) {
            return $this;
        }

        $this->totalNumberOfLinksAdded++;

        if (null === $this->links) {
            $this->links = [];
        }

        if (count($this->links) === $this->spanLimits->getLinkCountLimit()) {
            return $this;
        }

        $this->links[] = new Link(
            $context,
            Attributes::withLimits(
                $attributes,
                new AttributeLimits(
                    $this->spanLimits->getAttributePerLinkCountLimit(),
                    $this->spanLimits->getAttributeLimits()->getAttributeValueLengthLimit()
                )
            ),
        );

        return $this;
    }

    /** @inheritDoc */
    public function setAttribute(string $key, $value): API\SpanBuilderInterface
    {
        if (null === $this->attributes) {
            $this->attributes = Attributes::withLimits(new Attributes(), $this->spanLimits->getAttributeLimits());
        }

        $this->attributes->setAttribute($key, $value);

        return $this;
    }

    /** @inheritDoc */
    public function setAttributes(iterable $attributes): API\SpanBuilderInterface
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @psalm-param API\SpanKind::KIND_* $spanKind
     */
    public function setSpanKind(int $spanKind): API\SpanBuilderInterface
    {
        $this->spanKind = $spanKind;

        return $this;
    }

    /** @inheritDoc */
    public function setStartTimestamp(int $timestamp): API\SpanBuilderInterface
    {
        if (0 > $timestamp) {
            return $this;
        }

        $this->startEpochNanos = $timestamp;

        return $this;
    }

    /** @inheritDoc */
    public function startSpan(): API\SpanInterface
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
        $links = $this->links ?? [];
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

        $spanContext = API\SpanContext::create(
            $traceId,
            $spanId,
            SamplingResult::RECORD_AND_SAMPLE === $samplingDecision ? API\SpanContextInterface::TRACE_FLAG_SAMPLED : API\SpanContextInterface::TRACE_FLAG_DEFAULT,
            $samplingResultTraceState,
        );

        if (!in_array($samplingDecision, [SamplingResult::RECORD_AND_SAMPLE, SamplingResult::RECORD_ONLY], true)) {
            return Span::wrap($spanContext);
        }

        $samplingAttributes = $samplingResult->getAttributes();
        if ($samplingAttributes && $samplingAttributes->count() > 0) {
            foreach ($samplingAttributes as $key => $value) {
                $attributes->setAttribute($key, $value);
            }
        }

        return Span::startSpan(
            $this->spanName,
            $spanContext,
            $this->instrumentationLibrary,
            $this->spanKind,
            $parentSpan,
            $parentContext,
            $this->spanLimits,
            $this->tracerSharedState->getSpanProcessor(),
            $this->tracerSharedState->getResource(),
            $attributes,
            $links,
            $this->totalNumberOfLinksAdded,
            $this->startEpochNanos
        );
    }
}
