<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\TraceState;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\Tracer;
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

    public function test_span_should_receive_instrumentation_library(): void
    {
        $tracerProvider = new TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest', 'dev');
        /** @var Span $span */
        $span = $tracer->spanBuilder('test.span')->startSpan();
        $spanInstrumentationLibrary = $span->getInstrumentationLibrary();

        $this->assertEquals('OpenTelemetry.TracerTest', $spanInstrumentationLibrary->getName());
        $this->assertEquals('dev', $spanInstrumentationLibrary->getVersion());
    }

    public function test_span_builder_propagates_instrumentation_library_info_to_span(): void
    {
        /** @var Span $span */
        $span = (new TracerProvider())
            ->getTracer('name', 'version')
            ->spanBuilder('span')
            ->startSpan();

        $this->assertSame('name', $span->getInstrumentationLibrary()->getName());
        $this->assertSame('version', $span->getInstrumentationLibrary()->getVersion());
    }

    public function test_span_fall_back_name(): void
    {
        /** @var Span $span */
        $span = (new TracerProvider())
            ->getTracer('  ', 'version')
            ->spanBuilder('span')
            ->startSpan();

        $this->assertSame(Tracer::FALLBACK_SPAN_NAME, $span->getInstrumentationLibrary()->getName());
    }
}
