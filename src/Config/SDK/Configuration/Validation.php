<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration;

use Closure;
use InvalidArgumentException;
use function is_string;
use OpenTelemetry\SDK\Common\Configuration\Parser\MapParser;
use function preg_match;
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
     * Accepts a `key1=value1,key2=value2` list, as read by {@see MapParser}.
     */
    public static function ensureKeyValueList(): Closure
    {
        return static function (mixed $value): ?string {
            $value = self::ensureString()($value);
            if ($value !== null) {
                MapParser::parse($value);
            }

            return $value;
        };
    }

    /**
     * Accepts a Unix path, a Windows drive path or a UNC path.
     */
    public static function ensureAbsolutePath(): Closure
    {
        return static function (mixed $value): ?string {
            $value = self::ensureString()($value);
            if ($value !== null && !preg_match('#^(?:/|\\\\\\\\|[A-Za-z]:[/\\\\])#', $value)) {
                throw new InvalidArgumentException('must be an absolute path');
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
