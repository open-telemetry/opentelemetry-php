<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler;

final class OtelTraceState
{
    public const TRACE_STATE_KEY = 'ot';

    private const SUBKEY_RANDOM_VALUE = 'rv';
    private const SUBKEY_THRESHOLD = 'th';
    private const TRACE_STATE_SIZE_LIMIT = 256;

    private int $randomValue; // valid in the interval [0, MAX_RANDOM_VALUE]
    private int $threshold;    // valid in the interval [0, MAX_THRESHOLD]

    /**
     * @var string[]
     */
    private array $otherKeyValuePairs;

    /**
     * @param string[] $otherKeyValuePairs
     */
    private function __construct(int $randomValue, int $threshold, array $otherKeyValuePairs)
    {
        $this->randomValue = $randomValue;
        $this->threshold = $threshold;
        $this->otherKeyValuePairs = $otherKeyValuePairs;
    }

    public function invalidateRandomValue(): void
    {
        $this->randomValue = ConsistentSamplingUtil::getInvalidRandomValue();
    }

    public function invalidateThreshold(): void
    {
        $this->threshold = ConsistentSamplingUtil::getInvalidThreshold();
    }

    private static function isValueByte(string $c): bool
    {
        return self::isLowerCaseAlphaNum($c) || self::isUpperCaseAlpha($c) || $c === '.' || $c === '_' || $c === '-';
    }

    private static function isLowerCaseAlphaNum(string $c): bool
    {
        return self::isLowerCaseAlpha($c) || self::isDigit($c);
    }

    private static function isDigit(string $c): bool
    {
        return $c >= '0' && $c <= '9';
    }

    private static function isLowerCaseAlpha(string $c): bool
    {
        return $c >= 'a' && $c <= 'z';
    }

    private static function isUpperCaseAlpha(string $c): bool
    {
        return $c >= 'A' && $c <= 'Z';
    }

    private static function parseRandomValue(string $s, int $startIncl, int $endIncl): int
    {
        $len = $endIncl - $startIncl;
        if ($len !== 14) {
            return ConsistentSamplingUtil::getInvalidRandomValue();
        }

        return self::parseHex(
            $s,
            $startIncl,
            $len,
            ConsistentSamplingUtil::getInvalidRandomValue()
        );
    }

    /**
     * Parses a hexadecimal substring.
     *
     * @return int
     */
    private static function parseHex(string $s, int $startIncl, int $len, int $defaultValue): int
    {
        if ($len <= 0) {
            return $defaultValue;
        }

        $hex = substr($s, $startIncl, $len);
        if ($hex === false || $hex === '') {
            return $defaultValue;
        }

        if (!preg_match('/^[0-9a-fA-F]+$/', $hex)) {
            return $defaultValue;
        }

        // hexdec returns float on some platforms for large values, so cast carefully.
        $value = hexdec($hex);

        if (!is_int($value)) {
            if ($value > PHP_INT_MAX) {
                return $defaultValue;
            }
            $value = (int) $value;
        }

        return $value;
    }
}
