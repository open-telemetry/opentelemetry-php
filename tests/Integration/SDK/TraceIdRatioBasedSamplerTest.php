<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK;

use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class TraceIdRatioBasedSamplerTest extends TestCase
{
    public function test_never_trace_id_ratio_based_sampler_decision(): void
    {
        $sampler = new TraceIdRatioBasedSampler(0.0);
        $decision = $sampler->shouldSample(
            Context::getRoot(),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );
        $this->assertEquals(SamplingResult::DROP, $decision->getDecision());
    }

    public function test_always_trace_id_ratio_based_sampler_decision(): void
    {
        $sampler = new TraceIdRatioBasedSampler(1.0);
        $decision = $sampler->shouldSample(
            Context::getRoot(),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );
        $this->assertEquals(SamplingResult::RECORD_AND_SAMPLE, $decision->getDecision());
    }

    public function test_failing_trace_id_ratio_based_sampler_decision(): void
    {
        $sampler = new TraceIdRatioBasedSampler(0.99);
        $decision = $sampler->shouldSample(
            Context::getRoot(),
            '4bf92f3577b34da6a000000000000000',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );
        $this->assertEquals(SamplingResult::DROP, $decision->getDecision());
    }

    public function test_passing_trace_id_ratio_based_sampler_decision(): void
    {
        $sampler = new TraceIdRatioBasedSampler(0.01);
        $decision = $sampler->shouldSample(
            Context::getRoot(),
            '4bf92f3577b34da6afffffffffffffff',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );
        $this->assertEquals(SamplingResult::RECORD_AND_SAMPLE, $decision->getDecision());
    }

    public function test_ignore_parent_sampled_flag(): void
    {
        $parentTraceState = $this->createMock(API\TraceStateInterface::class);
        $sampler = new TraceIdRatioBasedSampler(0.0);
        $samplingResult = $sampler->shouldSample(
            $this->createParentContext(true, true, $parentTraceState),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );

        $this->assertEquals(SamplingResult::DROP, $samplingResult->getDecision());
        $this->assertEquals($parentTraceState, $samplingResult->getTraceState());
    }

    private function createParentContext(bool $sampled, bool $isRemote, ?API\TraceStateInterface $traceState = null): ContextInterface
    {
        $traceFlag = $sampled ? API\TraceFlags::SAMPLED : API\TraceFlags::DEFAULT;

        if ($isRemote) {
            $spanContext = SpanContext::createFromRemoteParent(
                '4bf92f3577b34da6a3ce929d0e0e4736',
                '00f067aa0ba902b7',
                $traceFlag,
                $traceState
            );
        } else {
            $spanContext = SpanContext::create(
                '4bf92f3577b34da6a3ce929d0e0e4736',
                '00f067aa0ba902b7',
                $traceFlag,
                $traceState
            );
        }

        return Context::getRoot()->withContextValue(new NonRecordingSpan($spanContext));
    }
}
