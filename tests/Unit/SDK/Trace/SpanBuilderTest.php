<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Trace\IdGeneratorInterface;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\SpanBuilder;
use OpenTelemetry\SDK\Trace\SpanLimits;
use OpenTelemetry\SDK\Trace\TracerSharedState;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Trace\SpanBuilder
 */
class SpanBuilderTest extends TestCase
{
    private SpanBuilder $spanBuilder;

    private InstrumentationScopeInterface $instrumentationScope;
    private TracerSharedState $tracerSharedState;
    private SpanLimits $spanLimits;
    private IdGeneratorInterface $idGenerator;
    private SamplerInterface $sampler;
    private SamplingResult $samplingResult;

    public function setUp(): void
    {
        $this->instrumentationScope = $this->createMock(InstrumentationScopeInterface::class);
        $this->tracerSharedState = $this->createMock(TracerSharedState::class);
        $this->spanLimits = $this->createMock(SpanLimits::class);
        $this->idGenerator = $this->createMock(IdGeneratorInterface::class);
        $this->sampler = $this->createMock(SamplerInterface::class);
        $this->samplingResult = $this->createMock(SamplingResult::class);
        $this->tracerSharedState->method('getSampler')->willReturn($this->sampler);
        $this->tracerSharedState->method('getIdGenerator')->willReturn($this->idGenerator);
        $this->sampler->method('shouldSample')->willReturn($this->samplingResult);

        $this->spanBuilder = new SpanBuilder('span', $this->instrumentationScope, $this->tracerSharedState, $this->spanLimits);
    }

    public function test_start_span(): void
    {
        $this->samplingResult->method('getDecision')->willReturn(SamplingResult::RECORD_AND_SAMPLE);
        $span = $this->spanBuilder->startSpan();
        $this->assertInstanceOf(SpanInterface::class, $span);
    }
}
