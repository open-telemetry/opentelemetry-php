<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\ImmutableSpan;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Trace\StatusDataInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Trace\ImmutableSpan
 */
class ImmutableSpanTest extends TestCase
{
    private Span $span;
    private AttributesInterface $attributes;
    private StatusDataInterface $status;
    private InstrumentationScope $instrumentationScope;
    private ResourceInfo $resource;
    private API\SpanContextInterface $context;
    private API\SpanContextInterface $parentContext;

    private string $traceId = 'trace-id';
    private string $spanId = 'span-id';
    private string $parentSpanId = 'parent-span-id';
    private int $endEpochNanos = 2000;
    private int $startEpochNanes = 1000;
    private int $totalRecordedEvents = 1;
    private int $totalRecordedLinks = 1;

    protected function setUp():void
    {
        $this->context = $this->createMock(API\SpanContextInterface::class);
        $this->parentContext = $this->createMock(API\SpanContextInterface::class);
        $this->instrumentationScope = $this->createMock(InstrumentationScope::class);
        $this->resource = $this->createMock(ResourceInfo::class);

        $this->span = $this->createMock(Span::class);
        $this->attributes = $this->createMock(AttributesInterface::class);
        $this->status = $this->createMock(StatusDataInterface::class);

        $this->span->method('getKind')->willReturn(SpanKind::KIND_INTERNAL);
        $this->span->method('getContext')->willReturn($this->context);
        $this->span->method('getParentContext')->willReturn($this->parentContext);
        $this->span->method('getInstrumentationScope')->willReturn($this->instrumentationScope);
        $this->span->method('getResource')->willReturn($this->resource);
        $this->span->method('getStartEpochNanos')->willReturn($this->startEpochNanes);
        $this->span->method('getTotalRecordedLinks')->willReturn($this->totalRecordedLinks);
        $this->context->method('getTraceId')->willReturn($this->traceId);
        $this->context->method('getSpanId')->willReturn($this->spanId);
        $this->parentContext->method('getSpanId')->willReturn($this->parentSpanId);
    }

    public function test_getters(): void
    {
        $span = new ImmutableSpan(
            $this->span,
            'name',
            [],
            [],
            $this->attributes,
            $this->totalRecordedEvents,
            $this->status,
            $this->endEpochNanos,
            false,
        );

        $this->assertSame(SpanKind::KIND_INTERNAL, $span->getKind());
        $this->assertSame($this->context, $span->getContext());
        $this->assertSame($this->parentContext, $span->getParentContext());
        $this->assertSame($this->traceId, $span->getTraceId());
        $this->assertSame($this->spanId, $span->getSpanId());
        $this->assertSame($this->parentSpanId, $span->getParentSpanId());
        $this->assertSame($this->startEpochNanes, $span->getStartEpochNanos());
        $this->assertSame($this->endEpochNanos, $span->getEndEpochNanos());
        $this->assertSame($this->instrumentationScope, $span->getInstrumentationScope());
        $this->assertSame($this->resource, $span->getResource());
        $this->assertSame('name', $span->getName());
        $this->assertSame([], $span->getLinks());
        $this->assertSame([], $span->getEvents());
        $this->assertSame($this->attributes, $span->getAttributes());
        $this->assertSame(1, $span->getTotalDroppedEvents());
        $this->assertSame(1, $span->getTotalDroppedLinks());
        $this->assertSame($this->status, $span->getStatus());
        $this->assertFalse($span->hasEnded());
    }
}
