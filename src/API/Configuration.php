<?php

declare(strict_types=1);

namespace OpenTelemetry\API;

use InvalidArgumentException;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Configuration\Parser\BooleanParser;
use OpenTelemetry\API\Configuration\Parser\ListParser;
use OpenTelemetry\API\Configuration\Parser\MapParser;
use OpenTelemetry\API\Configuration\Parser\RatioParser;
use OpenTelemetry\API\Configuration\Resolver\CompositeResolver;
use OpenTelemetry\API\Configuration\VariableTypes;
use UnexpectedValueException;

/**
 * Configuration can come from one or more of the following sources (from highest to lowest priority):
 * - values defined in `php.ini`
 * - environment variable (`$_SERVER` or `getenv`)
 * - `.env` file
 *
 * @psalm-internal \OpenTelemetry
 */
class Configuration
{
    use LogsMessagesTrait;

    public static function has(string $name): bool
    {
        return CompositeResolver::instance()->hasVariable($name);
    }

    public static function getInt(string $key, int $default = null): int
    {
        return (int) self::validateVariableValue(
            CompositeResolver::instance()->resolve(
                static::validateVariableType($key, VariableTypes::INTEGER),
                static::getDefault($key, $default)
            ),
            FILTER_VALIDATE_INT
        );
    }

    public static function getString(string $key, string $default = null): string
    {
        return (string) self::validateVariableValue(
            CompositeResolver::instance()->resolve(
                static::validateVariableType($key, VariableTypes::STRING),
                static::getDefault($key, $default)
            )
        );
    }

    public static function getBoolean(string $key, bool $default = null): bool
    {
        if ($default !== null) {
            $default = $default ? 'true' : 'false';
        }
        $resolved = self::validateVariableValue(
            CompositeResolver::instance()->resolve(
                static::validateVariableType($key, VariableTypes::BOOL),
                static::getDefault($key, $default)
            )
        );

        try {
            return BooleanParser::parse($resolved);
        } catch (InvalidArgumentException $e) {
            self::logWarning(sprintf('Invalid boolean value "%s" interpreted as "false" for %s', $resolved, $key));

            return false;
        }
    }

    public static function getMixed(string $key, $default = null)
    {
        return self::validateVariableValue(
            CompositeResolver::instance()->resolve(
                $key,
                static::getDefault($key, $default)
            )
        );
    }

    public static function getMap(string $key, array $default = null): array
    {
        return MapParser::parse(
            CompositeResolver::instance()->resolve(
                static::validateVariableType($key, VariableTypes::MAP),
                static::getDefault($key, $default)
            )
        );
    }

    public static function getList(string $key, array $default = null): array
    {
        return ListParser::parse(
            CompositeResolver::instance()->resolve(
                static::validateVariableType($key, VariableTypes::LIST),
                static::getDefault($key, $default)
            )
        );
    }

    public static function getEnum(string $key, string $default = null): string
    {
        return (string) self::validateVariableValue(
            CompositeResolver::instance()->resolve(
                static::validateVariableType($key, VariableTypes::ENUM),
                static::getDefault($key, $default)
            )
        );
    }

    public static function getFloat(string $key, float $default = null): float
    {
        return (float) self::validateVariableValue(
            CompositeResolver::instance()->resolve(
                static::validateVariableType($key, VariableTypes::FLOAT),
                static::getDefault($key, $default)
            ),
            FILTER_VALIDATE_FLOAT
        );
    }

    public static function getRatio(string $key, float $default = null): float
    {
        return RatioParser::parse(
            self::validateVariableValue(
                CompositeResolver::instance()->resolve(
                    static::validateVariableType($key, VariableTypes::RATIO),
                    static::getDefault($key, $default)
                )
            )
        );
    }

    public static function getDefault(string $key, $default)
    {
        return $default;
    }

    public static function getType(string $variableName): ?string
    {
        return VariableTypes::MIXED;
    }

    public static function isEmpty($value): bool
    {
        // don't use 'empty()', since '0' is not considered to be empty
        return $value === null || $value === '';
    }

    protected static function validateVariableType(string $variableName, string $type): string
    {
        return $variableName;
    }

    private static function validateVariableValue($value, ?int $filterType = null)
    {
        if ($filterType !== null && filter_var($value, $filterType) === false) {
            throw new UnexpectedValueException(sprintf('Value has invalid type "%s"', gettype($value)));
        }

        if ($value === null || $value === '') {
            throw new UnexpectedValueException(
                'Variable must not be null or empty'
            );
        }

        return $value;
    }
}
