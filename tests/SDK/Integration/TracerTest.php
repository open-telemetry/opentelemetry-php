<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Integration;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\NonRecordingSpan;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Trace\SpanContext;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\TraceState;
use PHPUnit\Framework\TestCase;

class TracerTest extends TestCase
{
    /**
     * @test
     */
    public function noopSpanShouldBeStartedWhenSamplingResultIsDrop(): void
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
                        API\SpanContextInterface::TRACE_FLAG_SAMPLED
                    )
                )
            );

        $newTraceState = new TraceState('new-key=new_value');

        $sampler = $this->createMock(SamplerInterface::class);
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
