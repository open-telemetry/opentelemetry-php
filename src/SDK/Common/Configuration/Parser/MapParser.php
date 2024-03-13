<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration\Parser;

use InvalidArgumentException;

class MapParser
{
    private const VARIABLE_SEPARATOR = ',';
    private const KEY_VALUE_SEPARATOR = '=';

    public static function parse($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        $result = [];

        if (null === $value || trim((string) $value) === '') {
            return $result;
        }

        foreach (explode(self::VARIABLE_SEPARATOR, (string) $value) as $pair) {
            self::validateKeyValuePair($pair);

            /** @psalm-suppress PossiblyUndefinedArrayOffset */
            [$key, $value] = explode(self::KEY_VALUE_SEPARATOR, $pair, 2);
            $result[trim($key)] = trim($value);
        }

        return $result;
    }

    private static function validateKeyValuePair(string $pair)
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
