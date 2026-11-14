<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use function assert;
use function hexdec;
use InvalidArgumentException;
use function pack;
use function sprintf;
use function substr;
use function unpack;

/**
 * Threshold and randomness math for consistent probability sampling.
 *
 * Thresholds and random values are 56-bit integers held in native PHP integers, so this requires 64-bit PHP
 * (as does the SDK as a whole, for example for its nanosecond timestamps).
 *
 * @internal
 *
 * @see https://opentelemetry.io/docs/specs/otel/trace/tracestate-probability-sampling/
 */
final class ConsistentSamplingUtil
{
    public const RANDOM_VALUE_BITS = 56;

    /** Rejection threshold of a sampler that never samples (2^56). */
    public const MAX_THRESHOLD = 1 << self::RANDOM_VALUE_BITS;

    /** Rejection threshold of a sampler that always samples. */
    public const MIN_THRESHOLD = 0;

    public const MAX_RANDOM_VALUE = self::MAX_THRESHOLD - 1;

    public const INVALID_RANDOM_VALUE = -1;

    private const HEX_DIGITS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'];

    private function __construct()
    {
    }

    /**
     * @throws InvalidArgumentException if the probability is not in [0, 1]
     */
    public static function calculateThreshold(float $probability): int
    {
        self::checkProbability($probability);

        return self::computeTValue($probability, 4, 4);
    }

    public static function isValidThreshold(int $threshold): bool
    {
        return $threshold >= self::MIN_THRESHOLD && $threshold <= self::MAX_THRESHOLD;
    }

    /**
     * Returns the explicit randomness value `rv` if it is valid, otherwise the least-significant 56 bits
     * of the trace ID, which samplers presume to be random.
     */
    public static function getRandomnessValue(OtelTraceState $otelTraceState, string $traceId): int
    {
        if ($otelTraceState->hasValidRandomValue()) {
            return $otelTraceState->getRandomValue();
        }

        return (int) hexdec(substr($traceId, -14));
    }

    public static function isValidRandomValue(int $randomValue): bool
    {
        return $randomValue >= 0 && $randomValue <= self::MAX_RANDOM_VALUE;
    }

    public static function isValidProbability(float $probability): bool
    {
        return $probability >= 0.0 && $probability <= 1.0;
    }

    /**
     * Encodes a threshold as lowercase hex without trailing zeros, for example 0x80000000000000 as "8".
     * MAX_THRESHOLD has no encoding because it does not fit in 56 bits.
     *
     * @throws InvalidArgumentException if the threshold is out of range or equals MAX_THRESHOLD
     */
    public static function encodeThreshold(int $threshold): string
    {
        self::checkThreshold($threshold);
        if ($threshold === self::MAX_THRESHOLD) {
            throw new InvalidArgumentException('Threshold MAX_THRESHOLD (no spans sampled) cannot be encoded.');
        }

        if ($threshold === 0) {
            return '0';
        }

        return self::hexWithoutTrailingZeros($threshold);
    }

    /**
     * Encodes a random value as exactly 14 lowercase hex digits.
     *
     * @throws InvalidArgumentException if the random value is out of range
     */
    public static function encodeRandomValue(int $randomValue): string
    {
        if (!self::isValidRandomValue($randomValue)) {
            throw new InvalidArgumentException(sprintf('Random value must be in [0, 2^56 - 1], got %d.', $randomValue));
        }

        $hex = '';
        for ($i = 52; $i >= 0; $i -= 4) {
            $hex .= self::HEX_DIGITS[($randomValue >> $i) & 0xf];
        }

        return $hex;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function checkThreshold(int $threshold): void
    {
        if (!self::isValidThreshold($threshold)) {
            throw new InvalidArgumentException(sprintf('Threshold must be in [0, 2^56], got %d.', $threshold));
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function checkProbability(float $probability): void
    {
        if (!self::isValidProbability($probability)) {
            throw new InvalidArgumentException(sprintf('Probability must be in [0, 1], got %f.', $probability));
        }
    }

    private static function hexWithoutTrailingZeros(int $value): string
    {
        // Bit 55 is set on a copy so that counting trailing zeros stops inside the 56 bits
        $anchored = $value | 0x80000000000000;
        $trailingZeroBits = 0;
        while (($anchored & 0xf) === 0) {
            $trailingZeroBits += 4;
            $anchored >>= 4;
        }

        $hex = '';
        for ($i = 52; $i >= $trailingZeroBits; $i -= 4) {
            $hex .= self::HEX_DIGITS[($value >> $i) & 0xf];
        }

        return $hex;
    }

    /**
     * Computes the 56-bit rejection threshold (T-value) for a given probability.
     *
     * The T-value is computed as `2**56*(1-$probability)` with a precision of
     * `2**-($wordSize*⌈-log2($probability)/$wordSize+$precision-1⌉)`.
     *
     * ```
     * 1/3 w/ precision=3, wordSize=4
     * => 1 - 1/3
     * => 2/3
     * => 2730.666../4096
     * => 2731/4096
     * => 0xaab
     * ```
     *
     * Converting the result into `th` hexadecimal value:
     * ```
     * $th = rtrim(bin2hex(substr(pack('J', $t), 1)), '0') ?: '0';
     * ```
     *
     * @param float $probability sampling probability, must be between 0 and 1
     * @param int $precision precision in words
     * @param int $wordSize word size to use, must be a power of two
     * @return int 56bit T-value, or `2**56` if out of sampling range
     *
     * @internal
     */
    public static function computeTValue(float $probability, int $precision, int $wordSize = 1): int
    {
        assert($probability >= 0 && $probability <= 1);
        assert($precision >= 1);
        assert($wordSize >= 1 && ($wordSize & $wordSize - 1) === 0);

        /** @psalm-suppress PossiblyInvalidArrayAccess */
        $b = unpack('J', pack('E', $probability))[1];
        $e = $b >> 52 & (1 << 11) - 1;
        $f = $b & (1 << 52) - 1 | ($e ? 1 << 52 : 0);

        // 56+1bit for rounding
        $s = $e - 1023 - 52 + 57;
        $t = (1 << 57) - ($s < 0 ? $f >> -$s : $f << $s);
        $m = -1 << 56 >> (-($e - 1023 + 1) + $precision * $wordSize & -$wordSize);

        return $t - $m >> 1 & $m;
    }
}
