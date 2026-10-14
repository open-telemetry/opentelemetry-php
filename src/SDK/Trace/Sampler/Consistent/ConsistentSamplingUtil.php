<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use function hexdec;
use InvalidArgumentException;

/**
 * Utility class for consistent probability sampling calculations.
 *
 * @experimental
 *
 * @see https://opentelemetry.io/docs/specs/otel/trace/tracestate-probability-sampling/
  *
 * Thresholds and random values are 56-bit integers held in native PHP integers, so this requires 64-bit PHP
 * (as does the SDK as a whole, e.g. its nanosecond timestamps).
 */
final class ConsistentSamplingUtil
{
    public const RANDOM_VALUE_BITS = 56;

    /** Maximum threshold value (= 0% sampling probability) */
    public const MAX_THRESHOLD = 1 << self::RANDOM_VALUE_BITS; // 2^56

    /** Minimum threshold value (= 100% sampling probability) */
    public const MIN_THRESHOLD = 0;

    /** Maximum valid random value */
    public const MAX_RANDOM_VALUE = self::MAX_THRESHOLD - 1;

    /** Sentinel for invalid threshold */
    public const INVALID_THRESHOLD = -1;

    /** Sentinel for invalid random value */
    public const INVALID_RANDOM_VALUE = -1;

    private const HEX_DIGITS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'];

    private function __construct()
    {
    }

    /**
     * Computes the rejection threshold from a sampling probability.
     *
     * threshold = MAX_THRESHOLD - round(probability * 2^56)
     *
     * @param float $probability Sampling probability in [0, 1]
     * @throws InvalidArgumentException if probability is out of range
     * @return int Threshold in [MIN_THRESHOLD, MAX_THRESHOLD]
     */
    public static function calculateThreshold(float $probability): int
    {
        self::checkProbability($probability);

        return (int) (self::MAX_THRESHOLD - (int) round($probability * (float) self::MAX_THRESHOLD));
    }

    /**
     * Returns the sampling probability for a given rejection threshold.
     *
     * @param int $threshold Threshold in [MIN_THRESHOLD, MAX_THRESHOLD]
     * @throws InvalidArgumentException if threshold is out of range
     * @return float Sampling probability in [0, 1]
     */
    public static function calculateSamplingProbability(int $threshold): float
    {
        self::checkThreshold($threshold);

        return (float) (self::MAX_THRESHOLD - $threshold) * (2 ** -56);
    }

    /**
     * Computes the adjusted count (reciprocal of sampling probability) for a given threshold.
     *
     * @param int $threshold Threshold in [MIN_THRESHOLD, MAX_THRESHOLD - 1]
     * @throws InvalidArgumentException if threshold is invalid or equals MAX_THRESHOLD (no spans sampled)
     * @return float Adjusted count
     */
    public static function calculateAdjustedCount(int $threshold): float
    {
        self::checkThreshold($threshold);
        if ($threshold === self::MAX_THRESHOLD) {
            throw new InvalidArgumentException('Cannot calculate adjusted count for maximum threshold (0 sampling probability).');
        }

        return (float) self::MAX_THRESHOLD / (float) (self::MAX_THRESHOLD - $threshold);
    }

    /**
     * Returns true if the given threshold is a valid threshold in [MIN_THRESHOLD, MAX_THRESHOLD].
     */
    public static function isValidThreshold(int $threshold): bool
    {
        return $threshold >= self::MIN_THRESHOLD && $threshold <= self::MAX_THRESHOLD;
    }

    /**
     * Returns R: the explicit randomness value `rv` from $otelTraceState (if valid), otherwise the
     * least-significant 56 bits of $traceId (14 hex characters), which samplers presume to be random.
     */
    public static function getRandomnessValue(OtelTraceState $otelTraceState, string $traceId): int
    {
        if ($otelTraceState->hasValidRandomValue()) {
            return $otelTraceState->getRandomValue();
        }

        // hexdec() of 14 hex chars = 56 bits, fits in PHP's 64-bit int
        return (int) hexdec(substr($traceId, -14));
    }

    /**
     * Returns true if the given random value is in [0, MAX_RANDOM_VALUE].
     */
    public static function isValidRandomValue(int $randomValue): bool
    {
        return $randomValue >= 0 && $randomValue <= self::MAX_RANDOM_VALUE;
    }

    /**
     * Returns true if the given probability is in [0, 1].
     */
    public static function isValidProbability(float $probability): bool
    {
        return $probability >= 0.0 && $probability <= 1.0;
    }

    /**
     * Encodes a 56-bit threshold as a hex string with trailing zeros removed.
     *
     * Examples: 0 → "0", 0x80000000000000 → "8", 0xfd70a3d70a3d71 → "fd70a3d70a3d71"
     *
     * {@see self::MAX_THRESHOLD} (no spans sampled) has no `th` encoding, as it does not fit in 56 bits.
     *
     * @throws InvalidArgumentException if threshold is out of range or equals MAX_THRESHOLD
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

        return self::appendLast56BitHexWithoutTrailingZeros($threshold);
    }

    /**
     * Encodes a 56-bit random value as exactly 14 hex characters (no trailing zero removal).
     *
     * @throws InvalidArgumentException if randomValue is out of range
     */
    public static function encodeRandomValue(int $randomValue): string
    {
        if (!self::isValidRandomValue($randomValue)) {
            throw new InvalidArgumentException(sprintf('Random value must be in [0, 2^56 - 1], got %d.', $randomValue));
        }

        return self::appendLast56BitHex($randomValue);
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

    /**
     * Encodes the last 56 bits of $value as exactly 14 hex chars.
     */
    private static function appendLast56BitHex(int $value): string
    {
        $buf = '';
        for ($i = 52; $i >= 0; $i -= 4) {
            $buf .= self::HEX_DIGITS[($value >> $i) & 0xf];
        }

        return $buf;
    }

    /**
     * Encodes the last 56 bits of $value as hex, stripping trailing zeros.
     */
    private static function appendLast56BitHexWithoutTrailingZeros(int $value): string
    {
        // Number of trailing zero bits (using 0x80000000000000 to anchor bit 55 so we get trailing zeros among the 56 bits)
        $anchoredValue = $value | 0x80000000000000;
        $numTrailingZeroBits = 0;
        $tmp = $anchoredValue;
        while (($tmp & 0xf) === 0) {
            $numTrailingZeroBits += 4;
            $tmp >>= 4;
        }

        $buf = '';
        for ($i = 52; $i >= $numTrailingZeroBits; $i -= 4) {
            $buf .= self::HEX_DIGITS[($value >> $i) & 0xf];
        }

        return $buf;
    }
}
