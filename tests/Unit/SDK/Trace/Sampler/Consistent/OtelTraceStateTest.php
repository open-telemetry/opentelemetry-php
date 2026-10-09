<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\Sampler\Consistent;

use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\OtelTraceState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function str_repeat;

#[CoversClass(OtelTraceState::class)]
class OtelTraceStateTest extends TestCase
{
    /**
     * Generate a repeated character string for length-boundary tests.
     */
    private static function xString(int $len): string
    {
        return str_repeat('X', $len);
    }

    public function test_empty_or_null_input(): void
    {
        $this->assertSame('', OtelTraceState::parse('')->serialize());
        $this->assertSame('', OtelTraceState::parse(null)->serialize());
    }

    public function test_invalid_input_returns_empty(): void
    {
        // Single char that is not a valid key
        $this->assertSame('', OtelTraceState::parse('a')->serialize());
        // Starts with '#'
        $this->assertSame('', OtelTraceState::parse('#')->serialize());
        // Space
        $this->assertSame('', OtelTraceState::parse(' ')->serialize());
        // Trailing semicolon is invalid
        $this->assertSame('', OtelTraceState::parse('th:0;')->serialize());
    }

    public function test_rv_roundtrip(): void
    {
        $this->assertSame('rv:1234567890abcd', OtelTraceState::parse('rv:1234567890abcd')->serialize());
        $this->assertSame('rv:01020304050607', OtelTraceState::parse('rv:01020304050607')->serialize());
    }

    public function test_rv_too_long_rejected(): void
    {
        // 15 hex chars → invalid
        $this->assertSame('', OtelTraceState::parse('rv:1234567890abcde')->serialize());
    }

    public function test_rv_too_short_rejected(): void
    {
        // 13 chars → invalid (must be exactly 14)
        $this->assertSame('', OtelTraceState::parse('rv:1000000000000')->serialize());
    }

    public function test_rv_non_hex_rejected(): void
    {
        $this->assertSame('', OtelTraceState::parse('rv:x')->serialize());
    }

    public function test_rv_upper_boundary(): void
    {
        $this->assertSame('rv:10000000000000', OtelTraceState::parse('rv:10000000000000')->serialize());
    }

    public function test_th_roundtrip(): void
    {
        $this->assertSame('th:1234567890abcd', OtelTraceState::parse('th:1234567890abcd')->serialize());
        $this->assertSame('th:01020304050607', OtelTraceState::parse('th:01020304050607')->serialize());
    }

    public function test_th_trailing_zeros_stripped_on_output(): void
    {
        // Input with 14 chars but trailing zeros → output drops trailing zeros
        $this->assertSame('th:1', OtelTraceState::parse('th:10000000000000')->serialize());
        $this->assertSame('th:12345', OtelTraceState::parse('th:1234500000000')->serialize());
        $this->assertSame('th:0', OtelTraceState::parse('th:0')->serialize());
    }

    public function test_th_too_long_rejected(): void
    {
        // 15 hex chars → invalid
        $this->assertSame('', OtelTraceState::parse('th:100000000000000')->serialize());
        $this->assertSame('', OtelTraceState::parse('th:1234567890abcde')->serialize());
    }

    public function test_th_non_hex_rejected(): void
    {
        $this->assertSame('', OtelTraceState::parse('th:x')->serialize());
    }

    public function test_th_various_lengths_with_trailing_zeros(): void
    {
        // Each of these has trailing zeros → serialized as 'th:1'
        $inputs = [
            'th:10000000000000',
            'th:1000000000000',
            'th:100000000000',
            'th:10000000000',
            'th:1000000000',
            'th:100000000',
            'th:10000000',
            'th:1000000',
            'th:100000',
            'th:10000',
            'th:1000',
            'th:100',
            'th:10',
            'th:1',
        ];
        foreach ($inputs as $input) {
            $this->assertSame('th:1', OtelTraceState::parse($input)->serialize(), "Failed for input: $input");
        }
    }

    public function test_th_with_non_trailing_significant_digits(): void
    {
        $this->assertSame('th:10000000000001', OtelTraceState::parse('th:10000000000001')->serialize());
        $this->assertSame('th:1000000000001', OtelTraceState::parse('th:10000000000010')->serialize());
    }

    public function test_combined_th_and_rv_reordered(): void
    {
        // Input has 'a:...;rv:...;th:...;x:3', output normalizes to 'th:...;rv:...;a:...;x:3'
        $xStr = self::xString(214);
        $input = "a:{$xStr};rv:1234567890abcd;th:1234567890abcd;x:3";
        $expected = "th:1234567890abcd;rv:1234567890abcd;a:{$xStr};x:3";
        $this->assertSame($expected, OtelTraceState::parse($input)->serialize());
    }

    public function test_exceeds_256_char_limit_returns_empty(): void
    {
        $xStr = self::xString(215);
        $input = "a:{$xStr};rv:1234567890abcd;th:1234567890abcd;x:3";
        $this->assertSame('', OtelTraceState::parse($input)->serialize());
    }

    public function test_set_threshold(): void
    {
        $state = OtelTraceState::parse('');
        $state->setThreshold(0x80000000000000);
        $this->assertSame('th:8', $state->serialize());
    }

    public function test_set_invalid_threshold_is_noop(): void
    {
        $state = OtelTraceState::parse('th:8');
        $state->setThreshold(ConsistentSamplingUtil::INVALID_THRESHOLD);
        $this->assertSame('', $state->serialize());
    }

    public function test_invalidate_threshold(): void
    {
        $state = OtelTraceState::parse('th:8');
        $this->assertTrue($state->hasValidThreshold());
        $state->invalidateThreshold();
        $this->assertFalse($state->hasValidThreshold());
        $this->assertSame('', $state->serialize());
    }

    public function test_invalidate_random_value(): void
    {
        $state = OtelTraceState::parse('rv:1234567890abcd');
        $this->assertTrue($state->hasValidRandomValue());
        $state->invalidateRandomValue();
        $this->assertFalse($state->hasValidRandomValue());
        $this->assertSame('', $state->serialize());
    }

    public function test_set_random_value(): void
    {
        $state = OtelTraceState::parse('');
        $state->setRandomValue(0x1234567890abcd);
        $this->assertSame('rv:1234567890abcd', $state->serialize());
    }

    public function test_other_key_value_pairs_preserved(): void
    {
        $state = OtelTraceState::parse('foo:bar;th:8;baz:qux');
        $this->assertSame('th:8;foo:bar;baz:qux', $state->serialize());
    }

    public function test_max_threshold_not_serialized(): void
    {
        // th = MAX_THRESHOLD means "drop" → not serialized
        $state = OtelTraceState::parse('');
        $state->setThreshold(ConsistentSamplingUtil::MAX_THRESHOLD);
        $this->assertSame('', $state->serialize());
    }

    public function test_has_valid_threshold(): void
    {
        $state = OtelTraceState::parse('th:8');
        $this->assertTrue($state->hasValidThreshold());
        $this->assertSame(0x80000000000000, $state->getThreshold());
    }

    public function test_has_valid_random_value(): void
    {
        $state = OtelTraceState::parse('rv:1234567890abcd');
        $this->assertTrue($state->hasValidRandomValue());
        // 14 hex chars left-aligned into 56-bit: 0x1234567890abcd
        $this->assertSame(0x1234567890abcd, $state->getRandomValue());
    }
}
