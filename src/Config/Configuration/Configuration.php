<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\Configuration;

use OpenTelemetry\Config\Configuration\Accessor\ClassConstantAccessor;
use OpenTelemetry\Config\Configuration\Parser\BooleanParser;
use OpenTelemetry\Config\Configuration\Parser\ListParser;
use OpenTelemetry\Config\Configuration\Parser\MapParser;
use OpenTelemetry\Config\Configuration\Parser\RatioParser;
use OpenTelemetry\Config\Configuration\Resolver\CompositeResolver;
use UnexpectedValueException;

/**
 * Configuration can come from one or more of the following sources (from highest to lowest priority):
 * - environment variable (getenv, $_SERVER)
 * - values defined in php.ini
 * - configuration file (todo)
 */
class Configuration
{
    public static function has(string $name): bool
    {
        return CompositeResolver::instance()->hasVariable($name);
    }

    public static function getInt(string $key, int $default = null): int
    {
        return (int) self::validateVariableValue(
            CompositeResolver::instance()->resolve($key, $default),
            FILTER_VALIDATE_INT
        );
    }

    public static function getString(string $key, string $default = null): string
    {
        return (string) self::validateVariableValue(
            CompositeResolver::instance()->resolve($key, $default),
        );
    }

    public static function getBoolean(string $key, bool $default = null): bool
    {
        $resolved = self::validateVariableValue(
            CompositeResolver::instance()->resolve(
                $key,
                null === $default ? $default : ($default ? 'true' : 'false')
            )
        );

        return BooleanParser::parse($resolved);
    }

    public static function getMixed(string $key, $default = null): mixed
    {
        return self::validateVariableValue(
            CompositeResolver::instance()->resolve(
                $key,
                $default
            )
        );
    }

    public static function getMap(string $key, array $default = null): array
    {
        return MapParser::parse(
            CompositeResolver::instance()->resolve($key, $default),
        );
    }

    public static function getList(string $key, array $default = null): array
    {
        return ListParser::parse(
            CompositeResolver::instance()->resolve($key, $default),
        );
    }

    public static function getEnum(string $key, string $default = null): string
    {
        return (string) self::validateVariableValue(
            CompositeResolver::instance()->resolve($key, $default),
        );
    }

    public static function getFloat(string $key, float $default = null): float
    {
        return (float) self::validateVariableValue(
            CompositeResolver::instance()->resolve($key, $default),
            FILTER_VALIDATE_FLOAT
        );
    }

    public static function getRatio(string $key, float $default = null): float
    {
        return RatioParser::parse(
            self::validateVariableValue(
                CompositeResolver::instance()->resolve($key, $default),
            )
        );
    }

    public static function getKnownValues(string $variableName): ?array
    {
        return ClassConstantAccessor::getValue(KnownValues::class, $variableName);
    }

    public static function getDefault(string $variableName)
    {
        return ClassConstantAccessor::getValue(Defaults::class, $variableName);
    }

    public static function isEmpty($value): bool
    {
        // don't use 'empty()', since '0' is not considered to be empty
        return $value === null || $value === '';
    }

    private static function validateVariableValue(mixed $value, ?int $filterType = null): mixed
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
