<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\TraceState;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Trace\SpanBuilder;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanLimitsBuilder;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use OpenTelemetry\SemConv\TraceAttributes;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversNothing]
class TracerTest extends TestCase
{
    use TestState;

    public function test_noop_span_should_be_started_when_sampling_result_is_drop(): void
    {
        $alwaysOffSampler = new AlwaysOffSampler();
        $processor = $this->createMock(SpanProcessorInterface::class);
        $tracerProvider = new TracerProvider($processor, $alwaysOffSampler);
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');

        $processor->expects($this->never())->method('onStart');
        $span = $tracer->spanBuilder('test.span')->startSpan();

        $this->assertInstanceOf(NonRecordingSpan::class, $span);
        $this->assertNotEquals(API\TraceFlags::SAMPLED, $span->getContext()->getTraceFlags());
    }

    public function test_sampler_may_override_parents_trace_state(): void
    {
        $parentTraceState = new TraceState('orig-key=orig_value');
        $parentContext = Context::getRoot()
            ->withContextValue(
                new NonRecordingSpan(
                    SpanContext::create(
                        '4bf92f3577b34da6a3ce929d0e0e4736',
                        '00f067aa0ba902b7',
                        API\TraceFlags::SAMPLED
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

    #[\PHPUnit\Framework\Attributes\Group('trace-compliance')]
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

    public function test_factory_returns_noop_tracer_when_sdk_disabled(): void
    {
        self::setEnvironmentVariable(Variables::OTEL_SDK_DISABLED, 'true');
        $tracerProvider = (new TracerProviderFactory())->create();
        $tracer = $tracerProvider->getTracer('foo');
        $this->assertInstanceOf(API\NoopTracer::class, $tracer);
    }

    public function test_general_identity_attributes_are_dropped_by_default(): void
    {
        $exporter = new InMemoryExporter();
        $tracerProvider = new TracerProvider(new SimpleSpanProcessor($exporter));
        $tracer = $tracerProvider->getTracer('test');
        $tracer->spanBuilder('test')
            ->setAttribute(TraceAttributes::ENDUSER_ID, 'username')
            ->setAttribute(TraceAttributes::ENDUSER_ROLE, 'admin')
            ->setAttribute(TraceAttributes::ENDUSER_SCOPE, 'read:message, write:files')
            ->startSpan()
            ->end();

        $tracerProvider->shutdown();

        /** @var AttributesInterface $attributes */
        $attributes = $exporter->getSpans()[0]->getAttributes();
        $this->assertCount(0, $attributes);
        $this->assertSame(3, $attributes->getDroppedAttributesCount());
    }

    public function test_general_identity_attributes_are_retained_if_enabled(): void
    {
        $exporter = new InMemoryExporter();
        $spanLimits = (new SpanLimitsBuilder())
            ->retainGeneralIdentityAttributes()
            ->build();
        $tracerProvider = new TracerProvider(new SimpleSpanProcessor($exporter), null, null, $spanLimits);
        $tracer = $tracerProvider->getTracer('test');
        $tracer->spanBuilder('test')
            ->setAttribute(TraceAttributes::ENDUSER_ID, 'username')
            ->setAttribute(TraceAttributes::ENDUSER_ROLE, 'admin')
            ->setAttribute(TraceAttributes::ENDUSER_SCOPE, 'read:message, write:files')
            ->startSpan()
            ->end();

        $tracerProvider->shutdown();

        /** @var AttributesInterface $attributes */
        $attributes = $exporter->getSpans()[0]->getAttributes();
        $this->assertCount(3, $attributes);
        $this->assertSame(0, $attributes->getDroppedAttributesCount());
    }
}
