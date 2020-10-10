<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Integration;

use InvalidArgumentException;
use OpenTelemetry\Sdk\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Trace as API;
use PHPUnit\Framework\TestCase;

class TraceIdRatioBasedSamplerTest extends TestCase
{
    public function testInvalidProbabilityTraceIdRatioBasedSampler()
    {
        $this->expectException(InvalidArgumentException::class);
        $sampler = new TraceIdRatioBasedSampler(-0.5);
        $this->expectException(InvalidArgumentException::class);
        $sampler = new TraceIdRatioBasedSampler(1.5);
    }

    public function testNeverTraceIdRatioBasedSamplerDecision()
    {
        $sampler = new TraceIdRatioBasedSampler(0.0);
        $decision = $sampler->shouldSample(
            null,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::NOT_RECORD, $decision->getDecision());
    }

    public function testAlwaysTraceIdRatioBasedSamplerDecision()
    {
        $sampler = new TraceIdRatioBasedSampler(1.0);
        $decision = $sampler->shouldSample(
            null,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::RECORD_AND_SAMPLED, $decision->getDecision());
    }

    public function testFailingTraceIdRatioBasedSamplerDecision()
    {
        $sampler = new TraceIdRatioBasedSampler(0.99);
        $decision = $sampler->shouldSample(
            null,
            '4bf92f3577b34da6afffffffffffffff',
            '00f067aa0ba902b7',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::NOT_RECORD, $decision->getDecision());
    }

    public function testPassingTraceIdRatioBasedSamplerDecision()
    {
        $sampler = new TraceIdRatioBasedSampler(0.01);
        $decision = $sampler->shouldSample(
            null,
            '4bf92f3577b34da6a000000000000000',
            '00f067aa0ba902b7',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::RECORD_AND_SAMPLED, $decision->getDecision());
    }

    public function testTraceIdRatioBasedSamplerDescription()
    {
        $sampler = new TraceIdRatioBasedSampler(0.0001);
        $this->assertEquals('TraceIdRatioBasedSampler{0.000100}', $sampler->getDescription());
    }
}
