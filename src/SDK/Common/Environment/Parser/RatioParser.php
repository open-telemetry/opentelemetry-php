<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Environment\Parser;

use InvalidArgumentException;
use RangeException;

class RatioParser
{
    private const MAX_VALUE = 1;
    private const MIN_VALUE = 0;

    public static function parse(string $value): float
    {
        if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
            throw new InvalidArgumentException(
                sprintf('Value "%s" contains non-numeric value', $value)
            );
        }

        $result = (float) $value;

        if ($result > self::MAX_VALUE || $result < self::MIN_VALUE) {
            throw new RangeException(
                sprintf(
                    'Value "%s" is not between %s and %s',
                    $value,
                    self::MIN_VALUE,
                    self::MAX_VALUE
                )
            );
        }

        return $result;
    }
}
