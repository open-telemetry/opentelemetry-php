<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration\Parser;

use InvalidArgumentException;

class BooleanParser
{
    private const TRUTHY_VALUES = [
        'true',
        'on',
        '1',
    ];

    private const FALSY_VALUES = [
        'false',
        'off',
        '0',
    ];

    /**
     * @param string|bool $value
     */
    public static function parse($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (in_array(strtolower($value), self::TRUTHY_VALUES)) {
            return true;
        }

        if (in_array(strtolower($value), self::FALSY_VALUES)) {
            return false;
        }

        throw new InvalidArgumentException(
            sprintf('Value "%s" is a non-boolean value', $value)
        );
    }
}
