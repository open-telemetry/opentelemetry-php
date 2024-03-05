<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\Parser;

class ListParser
{
    private const DEFAULT_SEPARATOR = ',';

    public static function parse(string|array $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (trim($value) === '') {
            return [];
        }

        return array_map(
            fn ($value) => trim($value),
            explode(self::DEFAULT_SEPARATOR, $value)
        );
    }
}
