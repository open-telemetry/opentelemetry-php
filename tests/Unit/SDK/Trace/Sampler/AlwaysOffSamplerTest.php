<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AlwaysOffSampler::class)]
class AlwaysOffSamplerTest extends TestCase
{
    public function test_should_sample(): void
    {
        $parentContext = Context::getRoot();
        $sampler = new AlwaysOffSampler();
        $decision = $sampler->shouldSample(
            $parentContext,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );

        $this->assertEquals(SamplingResult::DROP, $decision->getDecision());
    }

    public function test_get_description(): void
    {
        $sampler = new AlwaysOffSampler();
        $this->assertEquals('AlwaysOffSampler', $sampler->getDescription());
    }
}
