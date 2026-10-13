<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

/**
 * Parser and serializer for the `ot` sub-key of the W3C TraceState.
 *
 * Format: `th:<threshold>;rv:<randomvalue>[;other:value...]`
 * - `th`: 1–14 lower-case hex digits (right-padded with zeros to 56 bits), trailing zeros stripped on write
 * - `rv`: exactly 14 lower-case hex digits
 *
 * @internal
 *
 * @experimental
 *
 * @see https://opentelemetry.io/docs/specs/otel/trace/tracestate-handling/
 */
final class OtelTraceState
{
    public const TRACE_STATE_KEY = 'ot';

    private const SUBKEY_THRESHOLD = 'th';
    private const SUBKEY_RANDOM_VALUE = 'rv';
    private const TRACE_STATE_SIZE_LIMIT = 256;

    /** @var int valid in [0, MAX_RANDOM_VALUE], or INVALID_RANDOM_VALUE */
    private int $randomValue;

    /** @var int valid in [0, MAX_THRESHOLD], or INVALID_THRESHOLD */
    private int $threshold;

    /** @var list<string> other k:v pairs found in the `ot` value, preserved as-is */
    private readonly array $otherKeyValuePairs;

    private function __construct(int $randomValue, int $threshold, array $otherKeyValuePairs)
    {
        $this->randomValue = $randomValue;
        $this->threshold = $threshold;
        $this->otherKeyValuePairs = $otherKeyValuePairs;
    }

    private static function empty(): self
    {
        return new self(
            ConsistentSamplingUtil::INVALID_RANDOM_VALUE,
            ConsistentSamplingUtil::INVALID_THRESHOLD,
            [],
        );
    }

    public function getRandomValue(): int
    {
        return $this->randomValue;
    }

    public function getThreshold(): int
    {
        return $this->threshold;
    }

    public function hasValidRandomValue(): bool
    {
        return ConsistentSamplingUtil::isValidRandomValue($this->randomValue);
    }

    public function hasValidThreshold(): bool
    {
        return ConsistentSamplingUtil::isValidThreshold($this->threshold);
    }

    public function invalidateRandomValue(): void
    {
        $this->randomValue = ConsistentSamplingUtil::INVALID_RANDOM_VALUE;
    }

    public function invalidateThreshold(): void
    {
        $this->threshold = ConsistentSamplingUtil::INVALID_THRESHOLD;
    }

    /**
     * Sets the threshold. If the given value is invalid, the threshold is invalidated.
     */
    public function setThreshold(int $threshold): void
    {
        if (ConsistentSamplingUtil::isValidThreshold($threshold)) {
            $this->threshold = $threshold;
        } else {
            $this->invalidateThreshold();
        }
    }

    /**
     * Sets the random value. If the given value is invalid, the random value is invalidated.
     */
    public function setRandomValue(int $randomValue): void
    {
        if (ConsistentSamplingUtil::isValidRandomValue($randomValue)) {
            $this->randomValue = $randomValue;
        } else {
            $this->invalidateRandomValue();
        }
    }

    /**
     * Serializes the current state back to a string for the `ot` key.
     * Returns empty string if there is nothing to encode.
     */
    public function serialize(): string
    {
        $parts = [];

        if ($this->hasValidThreshold() && $this->threshold < ConsistentSamplingUtil::MAX_THRESHOLD) {
            $parts[] = self::SUBKEY_THRESHOLD . ':' . ConsistentSamplingUtil::encodeThreshold($this->threshold);
        }

        if ($this->hasValidRandomValue()) {
            $parts[] = self::SUBKEY_RANDOM_VALUE . ':' . ConsistentSamplingUtil::encodeRandomValue($this->randomValue);
        }

        // Append other k:v pairs, stopping if we would exceed the size limit
        $currentLength = $parts !== [] ? strlen(implode(';', $parts)) : 0;
        foreach ($this->otherKeyValuePairs as $pair) {
            $pairLength = ($currentLength > 0 ? 1 : 0) + strlen((string) $pair);
            if ($currentLength + $pairLength > self::TRACE_STATE_SIZE_LIMIT) {
                break;
            }
            $parts[] = $pair;
            $currentLength += $pairLength;
        }

        return implode(';', $parts);
    }

    /**
     * Parses an OtelTraceState from the value of the `ot` tracestate sub-key.
     *
     * Parsing is lenient: a member (`key:value`) that is syntactically invalid is discarded, the valid
     * members (including other vendors' sub-keys and `rv`) are preserved. An invalid `th` or `rv` value is
     * treated as absent. Members that would make the value exceed the 256 character limit are discarded.
     */
    public static function parse(?string $ts): self
    {
        if ($ts === null || $ts === '') {
            return self::empty();
        }

        $threshold = ConsistentSamplingUtil::INVALID_THRESHOLD;
        $randomValue = ConsistentSamplingUtil::INVALID_RANDOM_VALUE;
        $otherKeyValuePairs = [];
        $length = 0;

        foreach (explode(';', $ts) as $member) {
            if ($member === '') {
                continue;
            }

            $length += ($length > 0 ? 1 : 0) + strlen($member);
            if ($length > self::TRACE_STATE_SIZE_LIMIT) {
                break;
            }

            // key = lcalpha *(lcalpha / DIGIT); value = *(ucalpha / lcalpha / DIGIT / "." / "_" / "-")
            if (preg_match('/\A([a-z][a-z0-9]*):([A-Za-z0-9._-]*)\z/', $member, $matches) !== 1) {
                continue;
            }

            [, $key, $value] = $matches;
            if ($key === self::SUBKEY_THRESHOLD) {
                $threshold = self::parseThreshold($value, 0, strlen($value));
            } elseif ($key === self::SUBKEY_RANDOM_VALUE) {
                $randomValue = self::parseRandomValue($value, 0, strlen($value));
            } else {
                $otherKeyValuePairs[] = $member;
            }
        }

        return new self($randomValue, $threshold, $otherKeyValuePairs);
    }

    private static function parseThreshold(string $s, int $start, int $len): int
    {
        if ($len === 0 || $len > 14) {
            return ConsistentSamplingUtil::INVALID_THRESHOLD;
        }

        // Parse the 1–14 hex digits (right-pad with zeros to 14 chars = 56 bits)
        return self::parseHex($s, $start, $len, ConsistentSamplingUtil::INVALID_THRESHOLD);
    }

    private static function parseRandomValue(string $s, int $start, int $len): int
    {
        if ($len !== 14) {
            return ConsistentSamplingUtil::INVALID_RANDOM_VALUE;
        }

        return self::parseHex($s, $start, $len, ConsistentSamplingUtil::INVALID_RANDOM_VALUE);
    }

    /**
     * Parses a variable-length lower-case hex substring into an integer,
     * left-aligning within a 56-bit field (i.e. first digit = bits 52-55).
     *
     * This matches the Java parseHex() in consistent56: `r |= x << (52 - (i << 2))`
     */
    private static function parseHex(string $s, int $start, int $len, int $invalid): int
    {
        $r = 0;
        for ($i = 0; $i < $len; $i++) {
            $c = ord($s[$start + $i]);
            if ($c >= 0x30 && $c <= 0x39) {       // '0'..'9'
                $x = $c - 0x30;
            } elseif ($c >= 0x61 && $c <= 0x66) { // 'a'..'f'
                $x = $c - 0x61 + 10;
            } else {
                return $invalid;
            }
            $r |= $x << (52 - ($i << 2));
        }

        return $r;
    }
}
