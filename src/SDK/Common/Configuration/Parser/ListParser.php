<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration\Parser;

class ListParser
{
    private const DEFAULT_SEPARATOR = ',';

    /**
     * @param string|array $value
     */
    public static function parse($value): array
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
