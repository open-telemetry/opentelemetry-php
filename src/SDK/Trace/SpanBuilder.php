<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\AttributesBuilderInterface;
use OpenTelemetry\SDK\AttributesFactoryInterface;
use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Resource\ResourceInfo;

final class SpanBuilder implements API\SpanBuilderInterface
{
    /**
     * @var non-empty-string
     * @readonly
     */
    private string $spanName;

    private InstrumentationLibrary $instrumentationLibrary;
    private ResourceInfo $resource;
    private SamplerInterface $sampler;
    private SpanProcessorInterface $spanProcessor;
    private IdGeneratorInterface $idGenerator;
    private AttributesFactoryInterface $linkAttributes;
    private AttributesFactoryInterface $eventAttributes;

    private ?Context $parentContext = null; // Null means use current context.

    /**
     * @psalm-var API\SpanKind::KIND_*
     */
    private int $spanKind = API\SpanKind::KIND_INTERNAL;

    /** @var list<LinkInterface> */
    private array $links = [];

    private AttributesBuilderInterface $attributes;
    private int $droppedLinksCount;
    private int $eventCountLimit;
    private ?int $startEpochNanos = null;

    /** @param non-empty-string $spanName */
    public function __construct(
        string $spanName,
        InstrumentationLibrary $instrumentationLibrary,
        ResourceInfo $resource,
        SamplerInterface $sampler,
        SpanProcessorInterface $spanProcessor,
        IdGeneratorInterface $idGenerator,
        AttributesBuilderInterface $attributes,
        AttributesFactoryInterface $linkAttributes,
        AttributesFactoryInterface $eventAttributes,
        int $linkCountLimit,
        int $eventCountLimit
    ) {
        $this->spanName = $spanName;
        $this->instrumentationLibrary = $instrumentationLibrary;
        $this->resource = $resource;
        $this->sampler = $sampler;
        $this->spanProcessor = $spanProcessor;
        $this->idGenerator = $idGenerator;
        $this->attributes = $attributes;
        $this->linkAttributes = $linkAttributes;
        $this->eventAttributes = $eventAttributes;
        $this->droppedLinksCount = -$linkCountLimit;
        $this->eventCountLimit = $eventCountLimit;
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
        if (++$this->droppedLinksCount > 0) {
            return $this;
        }

        $this->links[] = new Link(
            $context,
            $this->linkAttributes->builder($attributes)->build(),
        );

        return $this;
    }

    /** @inheritDoc */
    public function setAttribute(string $key, $value): API\SpanBuilderInterface
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /** @inheritDoc */
    public function setAttributes(iterable $attributes): API\SpanBuilderInterface
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
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
        $this->startEpochNanos = $timestamp;

        return $this;
    }

    /** @inheritDoc */
    public function startSpan(): API\SpanInterface
    {
        $parentContext = $this->parentContext ?? Context::getCurrent();
        $parentSpanContext = Span::fromContext($parentContext)->getContext();

        $spanId = $this->idGenerator->generateSpanId();
        $traceId = $parentSpanContext->isValid()
            ? $parentSpanContext->getTraceId()
            : $this->idGenerator->generateTraceId();

        $samplingResult = $this->sampler->shouldSample(
            $parentContext,
            $traceId,
            $this->spanName,
            $this->spanKind,
            $this->attributes->build(),
            $this->links,
        );

        $spanContext = API\SpanContext::create(
            $traceId,
            $spanId,
            $samplingResult->getSpanContextFlags(),
            $samplingResult->getTraceState(),
        );

        if (!$samplingResult->isRecording()) {
            return Span::wrap($spanContext);
        }

        $attributes = clone $this->attributes;
        foreach ($samplingResult->getAttributes() as $key => $value) {
            $attributes[$key] = $value;
        }

        return Span::startSpan(
            $this->spanName,
            $spanContext,
            $this->instrumentationLibrary,
            $this->spanKind,
            $parentSpanContext,
            $parentContext,
            $this->eventAttributes,
            $this->spanProcessor,
            $this->resource,
            $attributes,
            $this->links,
            $this->droppedLinksCount,
            $this->eventCountLimit,
            $this->startEpochNanos
        );
    }
}
