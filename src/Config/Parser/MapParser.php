<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\Parser;

use InvalidArgumentException;

class MapParser
{
    private const VARIABLE_SEPARATOR = ',';
    private const KEY_VALUE_SEPARATOR = '=';

    public static function parse(string|array|null $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        $result = [];

        if (null === $value || trim($value) === '') {
            return $result;
        }

        foreach (explode(self::VARIABLE_SEPARATOR, $value) as $pair) {
            self::validateKeyValuePair($pair);

            /** @psalm-suppress PossiblyUndefinedArrayOffset */
            [$key, $value] = explode(self::KEY_VALUE_SEPARATOR, $pair, 2);
            $result[trim($key)] = trim($value);
        }

        return $result;
    }

    private static function validateKeyValuePair(string $pair): void
    {
        if (!str_contains($pair, self::KEY_VALUE_SEPARATOR)) {
            throw new InvalidArgumentException(sprintf(
                'Key-Value pair "%s" does not contain separator "%s"',
                $pair,
                self::KEY_VALUE_SEPARATOR
            ));
        }
    }
}
