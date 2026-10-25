<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration;

use Closure;
use InvalidArgumentException;
use function is_string;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * Provides validation closures for {@see NodeDefinition}s.
 *
 * @see NodeDefinition::validate()
 */
final class Validation
{
    public static function ensureString(): Closure
    {
        return static function (mixed $value): ?string {
            if ($value === null) {
                return null;
            }
            if (!is_string($value)) {
                throw new InvalidArgumentException('must be of type string');
            }

            return $value;
        };
    }

    /**
     * @psalm-suppress InvalidReturnType,InvalidReturnStatement,InvalidOperand
     */
    public static function ensureNumber(): Closure
    {

        return static function (mixed $value): null|int|float {
            if ($value === null) {
                return null;
            }
            if (!is_numeric($value)) {
                throw new InvalidArgumentException('must be of type numeric');
            }

            return +$value;
        };
    }
}
