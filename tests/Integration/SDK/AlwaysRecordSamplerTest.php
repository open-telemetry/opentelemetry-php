<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysRecordSampler;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class AlwaysRecordSamplerTest extends TestCase
{
    public function test_always_on_always_record_sampler_decision(): void
    {
        $rootSampler = new AlwaysOnSampler();
        $sampler = new AlwaysRecordSampler($rootSampler);
        $decision = $sampler->shouldSample(
            Context::getRoot(),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );
        $this->assertEquals(SamplingResult::RECORD_AND_SAMPLE, $decision->getDecision());
    }

    public function test_always_off_always_record_sampler_decision(): void
    {
        $rootSampler = new AlwaysOffSampler();
        $sampler = new AlwaysRecordSampler($rootSampler);
        $decision = $sampler->shouldSample(
            Context::getRoot(),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );
        $this->assertEquals(SamplingResult::RECORD_ONLY, $decision->getDecision());
    }

    public function test_never_trace_id_ratio_based_always_record_sampler_decision(): void
    {
        $rootSampler = new TraceIdRatioBasedSampler(0.0);
        $sampler = new AlwaysRecordSampler($rootSampler);
        $decision = $sampler->shouldSample(
            Context::getRoot(),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );
        $this->assertEquals(SamplingResult::RECORD_ONLY, $decision->getDecision());
    }

    public function test_always_trace_id_ratio_based_always_record_sampler_decision(): void
    {
        $rootSampler = new TraceIdRatioBasedSampler(1.0);
        $sampler = new AlwaysRecordSampler($rootSampler);
        $decision = $sampler->shouldSample(
            Context::getRoot(),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );
        $this->assertEquals(SamplingResult::RECORD_AND_SAMPLE, $decision->getDecision());
    }
}
