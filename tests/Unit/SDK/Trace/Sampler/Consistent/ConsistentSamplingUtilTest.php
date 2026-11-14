<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler\Consistent;

use InvalidArgumentException;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\OtelTraceState;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsistentSamplingUtil::class)]
class ConsistentSamplingUtilTest extends TestCase
{
    public function test_sentinels_are_outside_the_valid_ranges(): void
    {
        $this->assertFalse(ConsistentSamplingUtil::isValidThreshold(SamplingIntent::INVALID_THRESHOLD));
        $this->assertFalse(ConsistentSamplingUtil::isValidRandomValue(ConsistentSamplingUtil::INVALID_RANDOM_VALUE));
        $this->assertTrue(ConsistentSamplingUtil::isValidThreshold(ConsistentSamplingUtil::MIN_THRESHOLD));
        $this->assertTrue(ConsistentSamplingUtil::isValidThreshold(ConsistentSamplingUtil::MAX_THRESHOLD));
        $this->assertTrue(ConsistentSamplingUtil::isValidRandomValue(ConsistentSamplingUtil::MAX_RANDOM_VALUE));
        $this->assertFalse(ConsistentSamplingUtil::isValidRandomValue(ConsistentSamplingUtil::MAX_THRESHOLD));
    }

    public function test_invalid_threshold_is_invalid(): void
    {
        $this->assertFalse(ConsistentSamplingUtil::isValidThreshold(SamplingIntent::INVALID_THRESHOLD));
    }

    public function test_invalid_random_value_is_invalid(): void
    {
        $this->assertFalse(ConsistentSamplingUtil::isValidRandomValue(ConsistentSamplingUtil::INVALID_RANDOM_VALUE));
    }

    public function test_calculate_threshold(): void
    {
        // probability 1.0 gives threshold 0 (always sample)
        $this->assertSame(0, ConsistentSamplingUtil::calculateThreshold(1.0));

        // probability 0.5 gives threshold 2^56 * 0.5 = 2^55 = 0x80000000000000
        $this->assertSame(0x80000000000000, ConsistentSamplingUtil::calculateThreshold(0.5));

        // probability 0.25 gives threshold 0xc0000000000000
        $this->assertSame(0xc0000000000000, ConsistentSamplingUtil::calculateThreshold(0.25));

        // probability 0.0 gives MAX_THRESHOLD (drop all)
        $this->assertSame(ConsistentSamplingUtil::MAX_THRESHOLD, ConsistentSamplingUtil::calculateThreshold(0.0));
    }

    public function test_calculate_threshold_invalid_probability(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ConsistentSamplingUtil::calculateThreshold(-0.01);
    }

    public function test_calculate_threshold_invalid_probability_over(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ConsistentSamplingUtil::calculateThreshold(1.01);
    }

    public function test_encode_threshold(): void
    {
        // 0 is encoded as "0" (always sample)
        $this->assertSame('0', ConsistentSamplingUtil::encodeThreshold(0));

        // 50% sampling is threshold 0x80000000000000, encoded as "8" (trailing zeros stripped)
        $this->assertSame('8', ConsistentSamplingUtil::encodeThreshold(0x80000000000000));

        // 25% is threshold 0xc0000000000000, encoded as "c"
        $this->assertSame('c', ConsistentSamplingUtil::encodeThreshold(0xc0000000000000));

        // All 14 hex digits significant
        $this->assertSame('03a436f7842456', ConsistentSamplingUtil::encodeThreshold(0x03a436f7842456));

        // Last 14 chars (56 bits), bit-shift truncation
        $this->assertSame('a436f7842456ab', ConsistentSamplingUtil::encodeThreshold(0x3a436f7842456ab & 0xffffffffffffff));
    }

    public function test_encode_threshold_rejects_max_threshold(): void
    {
        // 2^56 (no spans sampled) must not silently encode as "0" (always sample)
        $this->expectException(\InvalidArgumentException::class);
        ConsistentSamplingUtil::encodeThreshold(ConsistentSamplingUtil::MAX_THRESHOLD);
    }

    public function test_randomness_value_uses_rv_or_the_trace_id(): void
    {
        $traceId = '00112233445566778899aabbccddeeff'; // last 14 hex digits: 8899aabbccddeeff -> 99aabbccddeeff
        $withoutRv = OtelTraceState::parse('th:8');
        $withRv = OtelTraceState::parse('rv:1234567890abcd');

        $this->assertSame(0x99aabbccddeeff, ConsistentSamplingUtil::getRandomnessValue($withoutRv, $traceId));
        $this->assertSame(0x1234567890abcd, ConsistentSamplingUtil::getRandomnessValue($withRv, $traceId));
    }

    public function test_encode_random_value(): void
    {
        // 14 hex chars always, no trailing zero removal
        $this->assertSame('00000000000000', ConsistentSamplingUtil::encodeRandomValue(0));
        $this->assertSame('10000000000000', ConsistentSamplingUtil::encodeRandomValue(0x10000000000000));
        $this->assertSame('ffffffffffffff', ConsistentSamplingUtil::encodeRandomValue(0xffffffffffffff));
    }

    public function test_encode_random_value_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ConsistentSamplingUtil::encodeRandomValue(-1);
    }

    public function test_is_valid_threshold(): void
    {
        $this->assertTrue(ConsistentSamplingUtil::isValidThreshold(0));
        $this->assertTrue(ConsistentSamplingUtil::isValidThreshold(ConsistentSamplingUtil::MAX_THRESHOLD));
        $this->assertFalse(ConsistentSamplingUtil::isValidThreshold(-1));
        $this->assertFalse(ConsistentSamplingUtil::isValidThreshold(ConsistentSamplingUtil::MAX_THRESHOLD + 1));
    }

    public function test_is_valid_random_value(): void
    {
        $this->assertTrue(ConsistentSamplingUtil::isValidRandomValue(0));
        $this->assertTrue(ConsistentSamplingUtil::isValidRandomValue(ConsistentSamplingUtil::MAX_RANDOM_VALUE));
        $this->assertFalse(ConsistentSamplingUtil::isValidRandomValue(-1));
        $this->assertFalse(ConsistentSamplingUtil::isValidRandomValue(ConsistentSamplingUtil::MAX_THRESHOLD));
    }

    public function test_is_valid_probability(): void
    {
        $this->assertTrue(ConsistentSamplingUtil::isValidProbability(0.0));
        $this->assertTrue(ConsistentSamplingUtil::isValidProbability(0.5));
        $this->assertTrue(ConsistentSamplingUtil::isValidProbability(1.0));
        $this->assertFalse(ConsistentSamplingUtil::isValidProbability(-0.01));
        $this->assertFalse(ConsistentSamplingUtil::isValidProbability(1.01));
    }

    #[DataProvider('computeTValueProvider')]
    #[DataProvider('computeTValueProviderAdditional')]
    public function test_compute_t_value(string $expected, float $probability, int $precision = 4): void
    {
        $tv = ConsistentSamplingUtil::computeTValue($probability, $precision, 4);
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

    public static function computeTValueProviderAdditional(): iterable
    {
        yield ['ffffffffffffff', 2 ** -56];
        yield ['ffffffffffffff', (2 ** -56) + 2 ** -108];
        yield ['ffffffffffffff', (2 ** -57) + 2 ** -109];
        yield ['ffffffffffffff', 1.25 * 2 ** -57];
        yield ['ffffffffffffff', (1.25 * 2 ** -57) + 2 ** -109];
        yield ['ffffffffffffff', (1.25 * 2 ** -57) - 2 ** -109];
        yield ['fffffffffffffe', 1.75 * 2 ** -56];
        yield ['ffffffffff8', 65537 * 2 ** -57];
        yield ['fffffffff8', 65537 * 2 ** -53];

        yield ['fffffffffbffc', 4097 * 2 ** -50];
        yield ['fffffffffffbc', 17 * 2 ** -50];
        yield ['ffffffffbfff',  65539 * 2 ** -50];

        yield ['0',  0.0];
        yield ['0',  -0.0];
        yield ['0',  1.0];
        yield ['0',  2 ** -57];
        yield ['0',  2 ** -1022];
        yield ['0',  2 ** -1023];
        yield ['0',  2 ** -1074];
    }
}
