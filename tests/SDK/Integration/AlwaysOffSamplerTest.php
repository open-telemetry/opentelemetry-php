<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Integration;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\TraceState;
use PHPUnit\Framework\TestCase;

class AlwaysOffSamplerTest extends TestCase
{
    public function testAlwaysOffSampler(): void
    {
        $parentTraceState = $this->createMock(TraceState::class);
        $sampler = new AlwaysOffSampler();
        $decision = $sampler->shouldSample(
            $this->createParentContext(true, false, $parentTraceState),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );

        $this->assertEquals(SamplingResult::DROP, $decision->getDecision());
        $this->assertEquals($parentTraceState, $decision->getTraceState());
    }

    public function testAlwaysOnSamplerDescription(): void
    {
        $sampler = new AlwaysOffSampler();
        $this->assertEquals('AlwaysOffSampler', $sampler->getDescription());
    }

    private function createParentContext(bool $sampled, bool $isRemote, ?API\TraceStateInterface $traceState = null): Context
    {
        $traceFlag = $sampled ? API\SpanContextInterface::TRACE_FLAG_SAMPLED : API\SpanContextInterface::TRACE_FLAG_DEFAULT;

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
