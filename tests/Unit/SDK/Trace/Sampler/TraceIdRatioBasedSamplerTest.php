<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\Sampler;

use function bin2hex;
use InvalidArgumentException;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use function pack;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function rtrim;
use function substr;

#[CoversClass(TraceIdRatioBasedSampler::class)]
class TraceIdRatioBasedSamplerTest extends TestCase
{
    #[DataProvider('shouldSampleProvider')]
    public function test_should_sample(string $traceId, float $probability, int $result): void
    {
        $sampler = new TraceIdRatioBasedSampler($probability);
        $decision = $sampler->shouldSample(
            Context::getRoot(),
            $traceId,
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );
        $this->assertEquals($result, $decision->getDecision());
    }

    public static function shouldSampleProvider(): iterable
    {
        yield 'otep-0235' => ['123456789123456789d29d6a7215ced0', 0.25, SamplingResult::RECORD_AND_SAMPLE];

        yield 'tv=0' => ['4bf92f3577b34da6a3ce929d0e0e4736', 1.0, SamplingResult::RECORD_AND_SAMPLE];
        yield 'tv=8' => ['4bf92f3577b34da6a3ce929d0e0e4736', 0.5, SamplingResult::RECORD_AND_SAMPLE];
        yield 'tv=cccd' => ['4bf92f3577b34da6a3ce929d0e0e4736', 1 / 5, SamplingResult::RECORD_AND_SAMPLE];
        yield 'tv=d' => ['4bf92f3577b34da6a3ce929d0e0e4736', 3 / 16, SamplingResult::DROP];

        yield ['4bf92f3577b34da6a380000000000000', 0.5, SamplingResult::RECORD_AND_SAMPLE];
        yield ['4bf92f3577b34da6a37fffffffffffff', 0.5, SamplingResult::DROP];
        yield ['4bf92f3577b34da6a3f5560000000000', 1 / 24, SamplingResult::RECORD_AND_SAMPLE];
        yield ['4bf92f3577b34da6a3f554ffffffffff', 1 / 24, SamplingResult::DROP];
        yield ['4bf92f3577b34da6a3fffffffffffff0', 2 ** -52, SamplingResult::RECORD_AND_SAMPLE];
        yield ['4bf92f3577b34da6a3ffffffffffffef', 2 ** -52, SamplingResult::DROP];
        yield ['4bf92f3577b34da6a3ffffffffffffff', 2 ** -56, SamplingResult::RECORD_AND_SAMPLE];
        yield ['4bf92f3577b34da6a3fffffffffffffe', 2 ** -56, SamplingResult::DROP];
        yield ['4bf92f3577b34da6a3ffffffffffffff', 2 ** -57, SamplingResult::DROP];
    }

    #[DataProvider('computeTValueProvider')]
    public function test_compute_t_value(string $expected, float $probability, int $precision): void
    {
        $tv = TraceIdRatioBasedSampler::computeTValue($probability, $precision, 4);
        $this->assertSame($expected, rtrim(bin2hex(substr(pack('J', $tv), 1)), '0') ?: '0');
    }

    public static function computeTValueProvider(): iterable
    {
        // see https://github.com/open-telemetry/opentelemetry-specification/pull/4166
        yield from [['0', 1, 3], ['0', 1, 4], ['0', 1, 5]];
        yield from [['8', 1/2, 3], ['8', 1/2, 4], ['8', 1/2, 5]];
        yield from [['aab', 1/3, 3], ['aaab', 1/3, 4], ['aaaab', 1/3, 5]];
        yield from [['c', 1/4, 3], ['c', 1/4, 4], ['c', 1/4, 5]];
        yield from [['ccd', 1/5, 3], ['cccd', 1/5, 4], ['ccccd', 1/5, 5]];
        yield from [['e', 1/8, 3], ['e', 1/8, 4], ['e', 1/8, 5]];
        yield from [['e66', 1/10, 3], ['e666', 1/10, 4], ['e6666', 1/10, 5]];
        yield from [['f', 1/16, 3], ['f', 1/16, 4], ['f', 1/16, 5]];
        yield from [['fd71', 1/100, 3], ['fd70a', 1/100, 4], ['fd70a4', 1/100, 5]];
        yield from [['ffbe7', 1/1000, 3], ['ffbe77', 1/1000, 4], ['ffbe76d', 1/1000, 5]];
        yield from [['fff972', 1/10000, 3], ['fff9724', 1/10000, 4], ['fff97247', 1/10000, 5]];
        yield from [['ffff584', 1/100000, 3], ['ffff583a', 1/100000, 4], ['ffff583a5', 1/100000, 5]];
        yield from [['ffffef4', 1/1000000, 3], ['ffffef39', 1/1000000, 4], ['ffffef391', 1/1000000, 5]];
    }

    #[DataProvider('invalidProbabilityProvider')]
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
            'NaN' => [NAN],
        ];
    }

    public function test_get_description(): void
    {
        $sampler = new TraceIdRatioBasedSampler(0.0001);
        $this->assertEquals('TraceIdRatioBasedSampler{0.000100}', $sampler->getDescription());
    }
}
