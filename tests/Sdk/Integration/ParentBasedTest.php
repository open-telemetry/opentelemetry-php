<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Integration;

use OpenTelemetry\Sdk\Trace\Sampler\ParentBased;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Trace as API;
use PHPUnit\Framework\TestCase;

class ParentBasedTest extends TestCase
{
    public function testRecordParentBasedDecision()
    {
        $parentContext = new SpanContext(
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            0x1
        );
        $sampler = new ParentBased();
        $decision = $sampler->shouldSample(
            $parentContext,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::RECORD_AND_SAMPLED, $decision->getDecision());
    }

    public function testSkipParentBasedDecision()
    {
        $parentContext = new SpanContext(
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            0
        );
        $sampler = new ParentBased();
        $decision = $sampler->shouldSample(
            $parentContext,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::NOT_RECORD, $decision->getDecision());
    }

    public function testNullParentBasedDecision()
    {
        $sampler = new ParentBased();
        $decision = $sampler->shouldSample(
            null,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals(SamplingResult::NOT_RECORD, $decision->getDecision());
    }

    public function testParentBasedDescription()
    {
        $sampler = new ParentBased();
        $this->assertEquals('ParentBased', $sampler->getDescription());
    }
}
