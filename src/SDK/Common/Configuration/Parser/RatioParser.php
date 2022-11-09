<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration\Parser;

use InvalidArgumentException;
use RangeException;

class RatioParser
{
    private const MAX_VALUE = 1;
    private const MIN_VALUE = 0;

    public static function parse($value): float
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
                    'Value must not be lower than %s or higher than %s. Given: %s',
                    self::MIN_VALUE,
                    self::MAX_VALUE,
                    $value
                )
            );
        }

        return $result;
    }
}
