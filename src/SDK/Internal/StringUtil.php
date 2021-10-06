<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Internal;

use function function_exists;
use function mb_strpos;
use function strpos;

class StringUtil
{
    public static function substr(string $value, int $offset, int $length): string
    {
        if (function_exists('mb_substr')) {
            return \mb_substr($value, $offset, $length);
        }

        return \substr($value, $offset, $length);
    }

    public static function str_contains(string $haystack, string $needle): bool
    {
        if ('' === $needle) {
            return false;
        }

        if (function_exists('mb_strpos')) {
            return false !== mb_strpos($haystack, $needle);
        }

        return false !== strpos($haystack, $needle);
    }

    public static function strlen(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return \mb_strlen($value);
        }

        return \strlen($value);
    }
}
