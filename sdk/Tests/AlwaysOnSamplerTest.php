<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Tests;

use OpenTelemetry\Sdk\Trace\AlwaysOnSampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Trace as API;
use PHPUnit\Framework\TestCase;

class AlwaysOnSamplerTest extends TestCase
{
    public function testAlwaysOnSamplerDecision()
    {
        $sampler = new AlwaysOnSampler();
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
        $sampler = new AlwaysOnSampler();
        $this->assertEquals('AlwaysOnSampler', $sampler->getDescription());
    }
}
