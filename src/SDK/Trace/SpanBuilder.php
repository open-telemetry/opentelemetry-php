<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function in_array;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesBuilderInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

final class SpanBuilder implements API\SpanBuilderInterface
{
    /**
     * @var non-empty-string
     * @readonly
     */
    private string $spanName;

    /** @readonly */
    private InstrumentationScopeInterface $instrumentationScope;

    /** @readonly */
    private TracerSharedState $tracerSharedState;

    /** @var ContextInterface|false|null */
    private $parentContext = null;

    /**
     * @psalm-var API\SpanKind::KIND_*
     */
    private int $spanKind = API\SpanKind::KIND_INTERNAL;

    /** @var list<LinkInterface> */
    private array $links = [];

    private AttributesBuilderInterface $attributesBuilder;
    private int $totalNumberOfLinksAdded = 0;
    private int $startEpochNanos = 0;

    /** @param non-empty-string $spanName */
    public function __construct(
        string $spanName,
        InstrumentationScopeInterface $instrumentationScope,
        TracerSharedState $tracerSharedState
    ) {
        $this->spanName = $spanName;
        $this->instrumentationScope = $instrumentationScope;
        $this->tracerSharedState = $tracerSharedState;
        $this->attributesBuilder = $tracerSharedState->getSpanLimits()->getAttributesFactory()->builder();
    }

    /** @inheritDoc */
    public function setParent($context): API\SpanBuilderInterface
    {
        $this->parentContext = $context;

        return $this;
    }

    /** @inheritDoc */
    public function addLink(API\SpanContextInterface $context, iterable $attributes = []): API\SpanBuilderInterface
    {
        if (!$context->isValid()) {
            return $this;
        }

        $this->totalNumberOfLinksAdded++;

        if (count($this->links) === $this->tracerSharedState->getSpanLimits()->getLinkCountLimit()) {
            return $this;
        }

        $this->links[] = new Link(
            $context,
            $this->tracerSharedState
                ->getSpanLimits()
                ->getLinkAttributesFactory()
                ->builder($attributes)
                ->build(),
        );

        return $this;
    }

    /** @inheritDoc */
    public function setAttribute(string $key, $value): API\SpanBuilderInterface
    {
        $this->attributesBuilder[$key] = $value;

        return $this;
    }

    /** @inheritDoc */
    public function setAttributes(iterable $attributes): API\SpanBuilderInterface
    {
        foreach ($attributes as $key => $value) {
            $this->attributesBuilder[$key] = $value;
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
    public function setStartTimestamp(int $timestampNanos): API\SpanBuilderInterface
    {
        if (0 > $timestampNanos) {
            return $this;
        }

        $this->startEpochNanos = $timestampNanos;

        return $this;
    }

    /** @inheritDoc */
    public function startSpan(): API\SpanInterface
    {
        $parentContext = Context::resolve($this->parentContext);
        $parentSpan = Span::fromContext($parentContext);
        $parentSpanContext = $parentSpan->getContext();

        $spanId = $this->tracerSharedState->getIdGenerator()->generateSpanId();

        if (!$parentSpanContext->isValid()) {
            $traceId = $this->tracerSharedState->getIdGenerator()->generateTraceId();
        } else {
            $traceId = $parentSpanContext->getTraceId();
        }

        $samplingResult = $this
            ->tracerSharedState
            ->getSampler()
            ->shouldSample(
                $parentContext,
                $traceId,
                $this->spanName,
                $this->spanKind,
                $this->attributesBuilder->build(),
                $this->links,
            );
        $samplingDecision = $samplingResult->getDecision();
        $samplingResultTraceState = $samplingResult->getTraceState();

        $spanContext = API\SpanContext::create(
            $traceId,
            $spanId,
            SamplingResult::RECORD_AND_SAMPLE === $samplingDecision ? API\TraceFlags::SAMPLED : API\TraceFlags::DEFAULT,
            $samplingResultTraceState,
        );

        if (!in_array($samplingDecision, [SamplingResult::RECORD_AND_SAMPLE, SamplingResult::RECORD_ONLY], true)) {
            return Span::wrap($spanContext);
        }

        $attributesBuilder = clone $this->attributesBuilder;
        foreach ($samplingResult->getAttributes() as $key => $value) {
            $attributesBuilder[$key] = $value;
        }

        return Span::startSpan(
            $this->spanName,
            $spanContext,
            $this->instrumentationScope,
            $this->spanKind,
            $parentSpan,
            $parentContext,
            $this->tracerSharedState->getSpanLimits(),
            $this->tracerSharedState->getSpanProcessor(),
            $this->tracerSharedState->getResource(),
            $attributesBuilder,
            $this->links,
            $this->totalNumberOfLinksAdded,
            $this->startEpochNanos
        );
    }
}
