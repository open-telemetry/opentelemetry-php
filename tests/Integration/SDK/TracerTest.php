<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContextFactory;
use OpenTelemetry\API\Trace\TraceState;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Trace\SpanBuilder;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class TracerTest extends TestCase
{
    public function test_noop_span_should_be_started_when_sampling_result_is_drop(): void
    {
        $alwaysOffSampler = new AlwaysOffSampler();
        $processor = $this->createMock(SpanProcessorInterface::class);
        $tracerProvider = new TracerProvider($processor, $alwaysOffSampler);
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');

        $processor->expects($this->never())->method('onStart');
        $span = $tracer->spanBuilder('test.span')->startSpan();

        $this->assertInstanceOf(NonRecordingSpan::class, $span);
        $this->assertNotEquals(API\SpanContextInterface::TRACE_FLAG_SAMPLED, $span->getContext()->getTraceFlags());
    }

    public function test_sampler_may_override_parents_trace_state(): void
    {
        $parentTraceState = new TraceState('orig-key=orig_value');
        $parentContext = Context::getRoot()
            ->withContextValue(
                new NonRecordingSpan(
                    SpanContextFactory::create(
                        '4bf92f3577b34da6a3ce929d0e0e4736',
                        '00f067aa0ba902b7',
                        API\SpanContextInterface::TRACE_FLAG_SAMPLED
                    )
                )
            );

        $newTraceState = new TraceState('new-key=new_value');

        $sampler = $this->createMock(SamplerInterface::class);
        $sampler->method('shouldSample')
            ->willReturn(new SamplingResult(
                SamplingResult::RECORD_AND_SAMPLE,
                [],
                $newTraceState
            ));

        $tracerProvider = new TracerProvider([], $sampler);
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');
        $span = $tracer->spanBuilder('test.span')->setParent($parentContext)->startSpan();

        $this->assertNotEquals($parentTraceState, $span->getContext()->getTraceState());
        $this->assertEquals($newTraceState, $span->getContext()->getTraceState());
    }

    /**
     * @group trace-compliance
     */
    public function test_span_should_receive_instrumentation_scope(): void
    {
        $tracerProvider = new TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest', 'dev', 'http://url', ['foo' => 'bar']);
        /** @var Span $span */
        $span = $tracer->spanBuilder('test.span')->startSpan();
        $spanInstrumentationScope = $span->getInstrumentationScope();

        $this->assertSame('OpenTelemetry.TracerTest', $spanInstrumentationScope->getName());
        $this->assertSame('dev', $spanInstrumentationScope->getVersion());
        $this->assertSame('http://url', $spanInstrumentationScope->getSchemaUrl());
        $this->assertSame(['foo' => 'bar'], $spanInstrumentationScope->getAttributes()->toArray());
    }

    public function test_returns_noop_span_builder_after_shutdown(): void
    {
        $tracerProvider = new TracerProvider();
        $tracer = $tracerProvider->getTracer('foo');
        $this->assertInstanceOf(SpanBuilder::class, $tracer->spanBuilder('bar'));
        $tracerProvider->shutdown();
        $this->assertInstanceOf(API\NoopSpanBuilder::class, $tracer->spanBuilder('baz'));
    }
}
