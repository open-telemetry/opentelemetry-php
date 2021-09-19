<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Links;
use OpenTelemetry\Sdk\Trace\NoopSpan;
use OpenTelemetry\Sdk\Trace\ReadableSpan;
use OpenTelemetry\Sdk\Trace\Sampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\SpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Trace as API;
use PHPUnit\Framework\TestCase;

class TracerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Context::attach(new Context()); // clean up the current Context
    }

    private function readSpanAttributesArray(ReadableSpan $span): array
    {
        return array_map(function (API\Attribute $attribute) {
            return $attribute->getValue();
        }, iterator_to_array($span->getAttributes()));
    }

    /**
     * @test
     */
    public function spanProcessorsShouldBeCalledWhenNewSpanIsStarted(): void
    {
        $processor = $this->createMock(SpanProcessor::class);

        $tracerProvider = new TracerProvider();
        $tracerProvider->addSpanProcessor($processor);

        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');

        $processor->expects($this->once())->method('onStart')->with($this->isInstanceOf(Span::class));
        $tracer->startSpan('test.span');
    }

    /**
     * @test
     */
    public function spanProcessorsShouldBeCalledWhenSpanEnded(): void
    {
        $processor = $this->createMock(SpanProcessor::class);

        $tracerProvider = new TracerProvider();
        $tracerProvider->addSpanProcessor($processor);

        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');
        $span = $tracer->startSpan('test.span');

        $processor->expects($this->once())->method('onEnd')->with($this->equalTo($span));
        $span->end();
    }

    /**
     * @test
     */
    public function activeSpanFromCurrentContextShouldBeUsedAsParent(): void
    {
        $tracerProvider = new TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');
        $span1 = $tracer->startSpan('test.span.1');
        $span1->activate();
        $span2 = $tracer->startSpan('test.span.2');

        $this->assertEquals($span1->getContext()->getTraceId(), $span2->getContext()->getTraceId());
        $this->assertNotEquals($span1->getContext()->getSpanId(), $span2->getContext()->getSpanId());

        $span2ParentContext = $span2->getParent();
        $this->assertNotNull($span2ParentContext);
        $this->assertEquals($span1->getContext()->getSpanId(), $span2ParentContext->getSpanId());
    }

    /**
     * @test
     */
    public function spanAndParentContextShouldHaveIdenticalTraceId(): void
    {
        $parentSpan = new NoopSpan(new SpanContext(
            'faa0c74e14bd78114ec2bc447ad94ec9',
            '50a75f197c3de59a',
            SpanContext::TRACE_FLAG_SAMPLED
        ));
        $parentContext = (new Context());
        $parentContext = $parentContext->withContextValue($parentSpan);

        $tracerProvider = new TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');
        $childSpan = $tracer->startSpan('test.span', $parentContext);

        $this->assertEquals($parentSpan->getContext()->getTraceId(), $childSpan->getContext()->getTraceId());
        $this->assertNotEquals($parentSpan->getContext()->getSpanId(), $childSpan->getContext()->getSpanId());

        $parentSpanContext = $childSpan->getParent();
        $this->assertNotNull($parentSpanContext);
        $this->assertEquals($parentSpan->getContext()->getSpanId(), $parentSpanContext->getSpanId());
    }

    /**
     * @test
     */
    public function rootSpansShouldHaveDifferentTraceId(): void
    {
        $tracerProvider = new TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');
        $span1 = $tracer->startSpan('test.span.1');
        $span2 = $tracer->startSpan('test.span.2');

        $this->assertNotEquals($span1->getContext()->getTraceId(), $span2->getContext()->getTraceId());
    }

    /**
     * @test
     */
    public function spanProcessorsShouldBeCalledWhenNewSpanIsCreated(): void
    {
        $processor = $this->createMock(SpanProcessor::class);
        $processor->expects($this->once())->method('onStart');

        $tracerProvider = new TracerProvider();
        $tracerProvider->addSpanProcessor($processor);

        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');

        $tracer->startAndActivateSpan('test.span');
    }

    /**
     * @test
     */
    public function startSpanAttributesShouldBePropagatedToSpan(): void
    {
        $tracerProvider = new TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');

        $attributes = [
            'attribute-1' => 'value-1',
            'attribute-2' => 'value-2',
        ];
        $span = $tracer->startSpan('test.span', null, API\SpanKind::KIND_INTERNAL, new Attributes($attributes));

        $this->assertSame($attributes, $this->readSpanAttributesArray($span));
    }

    /**
     * @test
     */
    public function startSpanLinksShouldBePropagatedToSpan(): void
    {
        $tracerProvider = new TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');

        $links = new Links();
        $span = $tracer->startSpan('test.span', null, API\SpanKind::KIND_INTERNAL, null, $links);

        $this->assertEquals($links, $span->getLinks());
    }

    /**
     * @test
     */
    public function startingSpanRespectsSamplerAttributes(): void
    {
        $tracerProvider = new TracerProvider(null, new class() implements Sampler {
            public function shouldSample(Context $parentContext, string $traceId, string $spanName, int $spanKind, ?API\Attributes $attributes = null, ?API\Links $links = null): SamplingResult
            {
                return new SamplingResult(SamplingResult::RECORD_AND_SAMPLE, new Attributes(['attribute' => 'value']));
            }

            public function getDescription(): string
            {
                return 'test';
            }
        });

        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');
        $span1 = $tracer->startSpan('test.span.1');
        $span2 = $tracer->startAndActivateSpan('test.span.2');

        $this->assertEquals(['attribute' => 'value'], $this->readSpanAttributesArray($span1));
        $this->assertEquals(['attribute' => 'value'], $this->readSpanAttributesArray($span2));
    }
}
