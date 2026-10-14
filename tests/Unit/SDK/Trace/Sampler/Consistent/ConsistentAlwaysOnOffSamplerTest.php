<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentAlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentAlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsistentAlwaysOnSampler::class)]
#[CoversClass(ConsistentAlwaysOffSampler::class)]
class ConsistentAlwaysOnOffSamplerTest extends TestCase
{
    private const TRACE_ID = '4bf92f3577b34da6a3ce929d0e0e4736';

    public function test_always_on_intent(): void
    {
        $sampler = ConsistentSampler::alwaysOn();
        $intent = $sampler->getSamplingIntent(
            Context::getRoot(),
            'span',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );
        $this->assertSame(ConsistentSamplingUtil::MIN_THRESHOLD, $intent->getThreshold());
        $this->assertTrue($intent->isAdjustedCountReliable());
    }

    public function test_always_on_samples(): void
    {
        $sampler = ConsistentSampler::alwaysOn();
        $result = $sampler->shouldSample(
            Context::getRoot(),
            self::TRACE_ID,
            'span',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
    }

    public function test_always_on_is_singleton(): void
    {
        $this->assertSame(ConsistentSampler::alwaysOn(), ConsistentSampler::alwaysOn());
    }

    public function test_always_off_intent(): void
    {
        $sampler = ConsistentSampler::alwaysOff();
        $intent = $sampler->getSamplingIntent(
            Context::getRoot(),
            'span',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );
        $this->assertSame(ConsistentSamplingUtil::INVALID_THRESHOLD, $intent->getThreshold());
        $this->assertFalse($intent->isAdjustedCountReliable());
    }

    public function test_always_off_drops(): void
    {
        $sampler = ConsistentSampler::alwaysOff();
        $result = $sampler->shouldSample(
            Context::getRoot(),
            self::TRACE_ID,
            'span',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );
        $this->assertSame(SamplingResult::DROP, $result->getDecision());
    }

    public function test_always_off_is_singleton(): void
    {
        $this->assertSame(ConsistentSampler::alwaysOff(), ConsistentSampler::alwaysOff());
    }

    public function test_description(): void
    {
        $this->assertSame('ConsistentAlwaysOnSampler', ConsistentSampler::alwaysOn()->getDescription());
        $this->assertSame('ConsistentAlwaysOffSampler', ConsistentSampler::alwaysOff()->getDescription());
    }
}
