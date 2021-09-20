<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Integration;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Trace\NonRecordingSpan;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\TraceState;
use OpenTelemetry\Trace as API;
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

    private function createParentContext(bool $sampled, bool $isRemote, ?API\TraceState $traceState = null): Context
    {
        return (new Context())->withContextValue(
            new NonRecordingSpan(
                SpanContext::restore(
                    '4bf92f3577b34da6a3ce929d0e0e4736',
                    '00f067aa0ba902b7',
                    $sampled,
                    $isRemote,
                    $traceState
                )
            )
        );
    }
}
