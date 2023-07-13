<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\Sampler;

use InvalidArgumentException;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler
 */
class TraceIdRatioBasedSamplerTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::shouldSample
     */
    public function test_should_sample(): void
    {
        $sampler = new TraceIdRatioBasedSampler(1.0);
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

    /**
     * @covers ::__construct
     * @dataProvider invalidProbabilityProvider
     */
    public function test_invalid_probability_trace_id_ratio_based_sampler(float $probability): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TraceIdRatioBasedSampler($probability);
    }

    public static function invalidProbabilityProvider(): array
    {
        return [
            'negative' => [-0.05],
            'greater than one' => [1.5],
        ];
    }

    /**
     * @covers ::getDescription
     */
    public function test_get_description(): void
    {
        $sampler = new TraceIdRatioBasedSampler(0.0001);
        $this->assertEquals('TraceIdRatioBasedSampler{0.000100}', $sampler->getDescription());
    }
}
