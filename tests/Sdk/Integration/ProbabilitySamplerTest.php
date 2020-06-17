<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Integration;

use OpenTelemetry\Sdk\Trace\Sampler\ProbabilitySampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Trace as API;
use PHPUnit\Framework\TestCase;

class ProbabilitySamplerTest extends TestCase
{
    public function testNeverProbabilitySamplerDecision()
    {
        $sampler = new ProbabilitySampler(0.0);
        $decision = $sampler->shouldSample(
            null,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::NOT_RECORD, $decision->getDecision());
    }

    public function testAlwaysProbabilitySamplerDecision()
    {
        $sampler = new ProbabilitySampler(1.0);
        $decision = $sampler->shouldSample(
            null,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::RECORD_AND_SAMPLED, $decision->getDecision());
    }

    public function testAlwaysOnSamplerDescription()
    {
        $sampler = new ProbabilitySampler(0.0001);
        $this->assertEquals('ProbabilitySampler{0.000100}', $sampler->getDescription());
    }
}
