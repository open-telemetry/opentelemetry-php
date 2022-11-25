<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration\Parser;

use InvalidArgumentException;

class BooleanParser
{
    private const TRUE_VALUE = 'true';
    private const FALSE_VALUE = 'false';

    /**
     * @param string|bool $value
     */
    public static function parse($value): bool
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
