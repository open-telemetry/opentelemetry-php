<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class AlwaysOnSamplerTest extends TestCase
{
    public function test_always_on_sampler_decision(): void
    {
        $parentTraceState = $this->createMock(API\TraceStateInterface::class);
        $sampler = new AlwaysOnSampler();
        $decision = $sampler->shouldSample(
            $this->createParentContext(true, false, $parentTraceState),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );

        $this->assertEquals(SamplingResult::RECORD_AND_SAMPLE, $decision->getDecision());
        $this->assertEquals($parentTraceState, $decision->getTraceState());
    }

    public function test_always_on_sampler_description(): void
    {
        $sampler = new AlwaysOnSampler();
        $this->assertEquals('AlwaysOnSampler', $sampler->getDescription());
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

        return Context::getRoot()->withContextValue(new NonRecordingSpan($spanContext));
    }
}
