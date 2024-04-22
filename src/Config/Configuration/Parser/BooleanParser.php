<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\Configuration\Parser;

use InvalidArgumentException;

/**
 * @internal
 */
class BooleanParser
{
    private const TRUE_VALUE = 'true';
    private const FALSE_VALUE = 'false';

    public static function parse(string|bool $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (strtolower($value) === self::TRUE_VALUE) {
            return true;
        }

        if (strtolower($value) === self::FALSE_VALUE) {
            return false;
        }

        throw new InvalidArgumentException(
            sprintf('Value "%s" is a non-boolean value', $value)
        );
    }
}
