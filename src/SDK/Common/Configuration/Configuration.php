<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

use OpenTelemetry\SDK\Common\Configuration\Parser\BooleanParser;
use OpenTelemetry\SDK\Common\Configuration\Parser\ListParser;
use OpenTelemetry\SDK\Common\Configuration\Parser\MapParser;
use OpenTelemetry\SDK\Common\Configuration\Parser\RatioParser;
use OpenTelemetry\SDK\Common\Configuration\Resolver\CompositeResolver;
use UnexpectedValueException;

/**
 * Configuration can come from one or more of the following sources (from highest to lowest priority):
 * - values defined in php.ini
 * - environment variable
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
            CompositeResolver::instance()->resolveValue(
                self::validateVariableType($key, VariableTypes::INTEGER),
                $default
            ),
            FILTER_VALIDATE_INT
        );
        //return Accessor::getInt($key, (string) $default);
    }

    public static function getString(string $key, string $default = ''): string
    {
        return (string) self::validateVariableValue(
            CompositeResolver::instance()->resolveValue(
                self::validateVariableType($key, VariableTypes::STRING),
                $default
            )
        );
        //return Accessor::getString($key, $default);
    }

    public static function getBoolean(string $key, bool $default = null): bool
    {
        return BooleanParser::parse(
            self::validateVariableValue(
                CompositeResolver::instance()->resolveValue(
                    self::validateVariableType($key, VariableTypes::BOOL),
                    $default
                )
            )
        );
        //return Accessor::getBool($key, null === $default ? null : ($default ? 'true' : 'false'));
    }

    public static function getMixed(string $key, string $default = null)
    {
        return self::validateVariableValue(
            CompositeResolver::instance()->resolveValue(
                $key,
                $default
            )
        );
    }

    public static function getMap(string $key, string $default = null): array
    {
        return MapParser::parse(
            CompositeResolver::instance()->resolveValue(
                self::validateVariableType($key, VariableTypes::MAP),
                $default
            )
        );
        //return Accessor::getMap($key, $default);
    }

    public static function getList(string $key, string $default = null): array
    {
        return ListParser::parse(
            CompositeResolver::instance()->resolveValue(
                self::validateVariableType($key, VariableTypes::LIST),
                $default
            )
        );
        //return Accessor::getList($key, $default);
    }

    public static function getEnum(string $key, string $default = null): string
    {
        return (string) self::validateVariableValue(
            CompositeResolver::instance()->resolveValue(
                self::validateVariableType($key, VariableTypes::ENUM),
                $default
            )
        );
        //return Accessor::getEnum($key, $default);
    }

    public static function getRatio(string $key, float $default = null): float
    {
        return RatioParser::parse(
            self::validateVariableValue(
                CompositeResolver::instance()->resolveValue(
                    self::validateVariableType($key, VariableTypes::RATIO),
                    $default
                )
            )
        );
        //return Accessor::getRatio($key, $default ? (string) $default : null);
    }

    public static function getFloat(string $key, string $default = null): float
    {
        return (float) self::validateVariableValue(
            CompositeResolver::instance()->resolveValue(
                self::validateVariableType($key, VariableTypes::FLOAT),
                $default
            ),
            FILTER_VALIDATE_FLOAT
        );
    }

    private static function validateVariableType(string $variableName, string $type): string
    {
        $variableType = Resolver::getType($variableName);

        if ($variableType !== null && $variableType !== $type && $variableType !== VariableTypes::MIXED) {
            throw new UnexpectedValueException(
                sprintf('Variable "%s" is not supposed to be of type "%s" but type "%s"', $variableName, $type, $variableType)
            );
        }

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
