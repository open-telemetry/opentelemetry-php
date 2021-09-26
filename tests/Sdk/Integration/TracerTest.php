<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Integration\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Trace\NonRecordingSpan;
use OpenTelemetry\Sdk\Trace\Sampler;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\SpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Sdk\Trace\TraceState;
use OpenTelemetry\Trace as API;
use PHPUnit\Framework\TestCase;

class TracerTest extends TestCase
{
    /**
     * @test
     */
    public function noopSpanShouldBeStartedWhenSamplingResultIsDrop(): void
    {
        $alwaysOffSampler = new AlwaysOffSampler();
        $processor = $this->createMock(SpanProcessor::class);
        $tracerProvider = new TracerProvider($processor, $alwaysOffSampler);
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');

        $processor->expects($this->never())->method('onStart');
        $span = $tracer->spanBuilder('test.span')->startSpan();

        $this->assertInstanceOf(NonRecordingSpan::class, $span);
        $this->assertNotEquals(API\SpanContext::TRACE_FLAG_SAMPLED, $span->getContext()->getTraceFlags());
    }

    /**
     * @test
     */
    public function samplerMayOverrideParentsTraceState(): void
    {
        $parentTraceState = new TraceState('orig-key=orig_value');
        $parentContext = (new Context())
            ->withContextValue(
                new NonRecordingSpan(
                    SpanContext::create(
                        '4bf92f3577b34da6a3ce929d0e0e4736',
                        '00f067aa0ba902b7',
                        API\SpanContext::TRACE_FLAG_SAMPLED
                    )
                )
            );

        $newTraceState = new TraceState('new-key=new_value');

        $sampler = $this->createMock(Sampler::class);
        $sampler->method('shouldSample')
            ->willReturn(new SamplingResult(
                SamplingResult::RECORD_AND_SAMPLE,
                null,
                $newTraceState
            ));

        $tracerProvider = new TracerProvider([], $sampler);
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');
        $span = $tracer->spanBuilder('test.span')->setParent($parentContext)->startSpan();

        $this->assertNotEquals($parentTraceState, $span->getContext()->getTraceState());
        $this->assertEquals($newTraceState, $span->getContext()->getTraceState());
    }

    /**
     * @test
     */
    public function spanShouldReceiveInstrumentationLibrary(): void
    {
        $tracerProvider = new TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest', 'dev');
        /** @var Span $span */
        $span = $tracer->spanBuilder('test.span')->startSpan();
        $spanInstrumentationLibrary = $span->getInstrumentationLibrary();

        $this->assertEquals('OpenTelemetry.TracerTest', $spanInstrumentationLibrary->getName());
        $this->assertEquals('dev', $spanInstrumentationLibrary->getVersion());
    }
}
