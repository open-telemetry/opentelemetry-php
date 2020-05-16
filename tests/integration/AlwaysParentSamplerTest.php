<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Tests;

use OpenTelemetry\Sdk\Trace\AlwaysOnSampler;
use OpenTelemetry\Sdk\Trace\AlwaysParentSampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Trace as API;
use PHPUnit\Framework\TestCase;

class AlwaysParentSamplerTest extends TestCase
{
    public function testRecordAlwaysParentSamplerDecision()
    {
        $parentContext = new SpanContext(
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            0x1
        );
        $sampler = new AlwaysParentSampler();
        $decision = $sampler->shouldSample(
            $parentContext,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::RECORD_AND_SAMPLED, $decision->getDecision());
    }

    public function testSkipAlwaysParentSamplerDecision()
    {
        $parentContext = new SpanContext(
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            0
        );
        $sampler = new AlwaysParentSampler();
        $decision = $sampler->shouldSample(
            $parentContext,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::NOT_RECORD, $decision->getDecision());
    }

    public function testNullAlwaysParentSamplerDecision()
    {
        $sampler = new AlwaysParentSampler();
        $decision = $sampler->shouldSample(
            null,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::NOT_RECORD, $decision->getDecision());
    }

    public function testAlwaysOnSamplerDescription()
    {
        $sampler = new AlwaysOnSampler();
        $this->assertEquals('AlwaysOnSampler', $sampler->getDescription());
    }
}
