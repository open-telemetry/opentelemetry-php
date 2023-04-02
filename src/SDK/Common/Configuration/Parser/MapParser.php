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

        if (null === $value || trim($value) === '') {
            return $result;
        }

        foreach (explode(self::VARIABLE_SEPARATOR, $value) as $pair) {
            self::validateKeyValuePair($pair);

            [$key, $value] = explode(self::KEY_VALUE_SEPARATOR, $pair, 2);
            $result[trim($key)] = trim($value);
        }

        return $result;
    }

    private static function validateKeyValuePair(string $pair)
    {
        if (strpos($pair, self::KEY_VALUE_SEPARATOR) === false) {
            throw new InvalidArgumentException(sprintf(
                'Key-Value pair "%s" does not contain separator "%s"',
                $pair,
                self::KEY_VALUE_SEPARATOR
            ));
        }
    }
}
