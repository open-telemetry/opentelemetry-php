<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler
 */
class AlwaysOnSamplerTest extends TestCase
{
    /**
     * @covers ::shouldSample
     */
    public function test_should_sample(): void
    {
        $parentContext = $this->createMock(Context::class);
        $sampler = new AlwaysOnSampler();
        $decision = $sampler->shouldSample(
            $parentContext,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );

        $this->assertEquals(SamplingResult::RECORD_AND_SAMPLE, $decision->getDecision());
    }

    /**
     * @covers ::getDescription
     */
    public function test_get_description(): void
    {
        $sampler = new AlwaysOnSampler();
        $this->assertEquals('AlwaysOnSampler', $sampler->getDescription());
    }
}
