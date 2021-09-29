<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Integration;

use InvalidArgumentException;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Trace\NonRecordingSpan;
use OpenTelemetry\Sdk\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\TraceState;
use OpenTelemetry\Trace as API;
use PHPUnit\Framework\TestCase;

class TraceIdRatioBasedSamplerTest extends TestCase
{
    public function testInvalidProbabilityTraceIdRatioBasedSampler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $sampler = new TraceIdRatioBasedSampler(-0.5);
        $this->expectException(InvalidArgumentException::class);
        $sampler = new TraceIdRatioBasedSampler(1.5);
    }

    public function testNeverTraceIdRatioBasedSamplerDecision(): void
    {
        $sampler = new TraceIdRatioBasedSampler(0.0);
        $decision = $sampler->shouldSample(
            new Context(),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::DROP, $decision->getDecision());
    }

    public function testAlwaysTraceIdRatioBasedSamplerDecision(): void
    {
        $sampler = new TraceIdRatioBasedSampler(1.0);
        $decision = $sampler->shouldSample(
            new Context(),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::RECORD_AND_SAMPLE, $decision->getDecision());
    }

    public function testFailingTraceIdRatioBasedSamplerDecision(): void
    {
        $sampler = new TraceIdRatioBasedSampler(0.99);
        $decision = $sampler->shouldSample(
            new Context(),
            '4bf92f3577b34da6afffffffffffffff',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::DROP, $decision->getDecision());
    }

    public function testPassingTraceIdRatioBasedSamplerDecision(): void
    {
        $sampler = new TraceIdRatioBasedSampler(0.01);
        $decision = $sampler->shouldSample(
            new Context(),
            '4bf92f3577b34da6a000000000000000',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::RECORD_AND_SAMPLE, $decision->getDecision());
    }

    public function testIgnoreParentSampledFlag(): void
    {
        $parentTraceState = $this->createMock(TraceState::class);
        $sampler = new TraceIdRatioBasedSampler(0.0);
        $samplingResult = $sampler->shouldSample(
            $this->createParentContext(true, true, $parentTraceState),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );

        $this->assertEquals(SamplingResult::DROP, $samplingResult->getDecision());
        $this->assertEquals($parentTraceState, $samplingResult->getTraceState());
    }

    public function testTraceIdRatioBasedSamplerDescription(): void
    {
        $sampler = new TraceIdRatioBasedSampler(0.0001);
        $this->assertEquals('TraceIdRatioBasedSampler{0.000100}', $sampler->getDescription());
    }

    private function createParentContext(bool $sampled, bool $isRemote, ?API\TraceState $traceState = null): Context
    {
        $traceFlag = $sampled ? API\SpanContext::TRACE_FLAG_SAMPLED : API\SpanContext::TRACE_FLAG_DEFAULT;

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

        return (new Context())->withContextValue(new NonRecordingSpan($spanContext));
    }
}
