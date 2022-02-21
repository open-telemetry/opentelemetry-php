<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Environment\Parser;

class ListParser
{
    private const DEFAULT_SEPARATOR = ',';

    public static function parse(string $value, string $separator = self::DEFAULT_SEPARATOR): array
    {
        if (trim($value) === '') {
            return [];
        }

        return array_map(
            fn ($value) => trim($value),
            explode(self::DEFAULT_SEPARATOR, $value),
        );
    }
}
