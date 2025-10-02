<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function in_array;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesBuilderInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppressor;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressor;

final class SpanBuilder implements API\SpanBuilderInterface
{
    private ContextInterface|false|null $parentContext = null;

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
        private readonly string $spanName,
        private readonly InstrumentationScopeInterface $instrumentationScope,
        private readonly TracerSharedState $tracerSharedState,
        private readonly SpanSuppressor $spanSuppressor = new NoopSuppressor(),
    ) {
        $this->attributesBuilder = $this->tracerSharedState->getSpanLimits()->getAttributesFactory()->builder();
    }

    /** @inheritDoc */
    #[\Override]
    public function setParent(ContextInterface|false|null $context): API\SpanBuilderInterface
    {
        $this->parentContext = $context;

        return $this;
    }

    /** @inheritDoc */
    #[\Override]
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
    #[\Override]
    public function setAttribute(string $key, mixed $value): API\SpanBuilderInterface
    {
        $this->attributesBuilder[$key] = $value;

        return $this;
    }

    /** @inheritDoc */
    #[\Override]
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
    #[\Override]
    public function setSpanKind(int $spanKind): API\SpanBuilderInterface
    {
        $this->spanKind = $spanKind;

        return $this;
    }

    /** @inheritDoc */
    #[\Override]
    public function setStartTimestamp(int $timestampNanos): API\SpanBuilderInterface
    {
        if (0 > $timestampNanos) {
            return $this;
        }

        $this->startEpochNanos = $timestampNanos;

        return $this;
    }

    /** @inheritDoc */
    #[\Override]
    public function startSpan(): API\SpanInterface
    {
        $parentContext = Context::resolve($this->parentContext);
        $parentSpan = Span::fromContext($parentContext);
        $parentSpanContext = $parentSpan->getContext();

        $spanSuppression = $this->spanSuppressor->resolveSuppression($this->spanKind, $this->attributesBuilder->build()->toArray());
        if ($spanSuppression->isSuppressed($parentContext)) {
            return Span::wrap($parentSpanContext);
        }

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
            // TODO must suppress no-op spans too
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
            $this->startEpochNanos,
            $spanSuppression,
        );
    }
}
