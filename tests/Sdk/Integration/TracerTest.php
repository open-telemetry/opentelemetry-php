<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Integration\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Trace\NoopSpan;
use OpenTelemetry\Sdk\Trace\Sampler;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Sdk\Trace\TraceState;
use OpenTelemetry\Trace\SpanContext;
use PHPUnit\Framework\TestCase;

class TracerTest extends TestCase
{
    /**
     * @test
     */
    public function noopSpanShouldBeStartedWhenSamplingResultIsDrop()
    {
        $alwaysOffSampler = new AlwaysOffSampler();
        $tracerProvider = new TracerProvider(null, $alwaysOffSampler);
        $processor = self::createMock(SpanProcessor::class);
        $tracerProvider->addSpanProcessor($processor);
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');

        $processor->expects($this->never())->method('onStart');
        $span = $tracer->startSpan('test.span');

        $this->assertInstanceOf(NoopSpan::class, $span);
        $this->assertNotEquals(SpanContext::TRACE_FLAG_SAMPLED, $span->getContext()->getTraceFlags());
    }

    /**
     * @test
     */
    public function samplerMayOverrideParentsTraceState()
    {
        $parentTraceState = new TraceState('orig-key=orig_value');
        $parentContext = Span::insert(new NoopSpan(\OpenTelemetry\Sdk\Trace\SpanContext::restore(
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            true,
            false,
            $parentTraceState
        )), new Context());

        $newTraceState = new TraceState('new-key=new_value');

        $sampler = $this->createMock(Sampler::class);
        $sampler->method('shouldSample')
            ->willReturn(new SamplingResult(
                SamplingResult::RECORD_AND_SAMPLE,
                null,
                $newTraceState
            ));

        $tracerProvider = new TracerProvider(null, $sampler);
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');
        $span = $tracer->startSpan('test.span', $parentContext);

        $this->assertNotEquals($parentTraceState, $span->getContext()->getTraceState());
        $this->assertEquals($newTraceState, $span->getContext()->getTraceState());
    }
}
