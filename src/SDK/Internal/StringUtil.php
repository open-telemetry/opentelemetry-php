<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Internal;

class StringUtil
{
    public static function substr(string $value, int $offset, int $length): string
    {
        if (function_exists('mb_substr')) {
            return \mb_substr($value, $offset, $length);
        }

        return \substr($value, $offset, $length);
    }

    public static function strlen(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return \mb_strlen($value);
        }

        return \strlen($value);
    }
}
