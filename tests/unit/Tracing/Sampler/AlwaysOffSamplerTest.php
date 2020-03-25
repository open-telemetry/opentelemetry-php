<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Tracing\Sampler;

use OpenTelemetry\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\Trace\Sampler\SamplingResult;
use PHPUnit\Framework\TestCase;

class AlwaysOffSamplerTest extends TestCase
{
    public function testAlwaysOffSampler()
    {
        $sampler = new AlwaysOffSampler();
        $decision = $sampler->shouldSample(
            null,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            'test.opentelemetry.io'
        );
        $this->assertEquals(SamplingResult::NOT_RECORD, $decision->getDecision());
    }

    public function testAlwaysOnSamplerDescription()
    {
        $sampler = new AlwaysOffSampler();
        $this->assertEquals('AlwaysOffSampler', $sampler->getDescription());
    }
}
