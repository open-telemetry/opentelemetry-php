<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

use InvalidArgumentException;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Configuration\Parser\BooleanParser;
use OpenTelemetry\SDK\Common\Configuration\Parser\ListParser;
use OpenTelemetry\SDK\Common\Configuration\Parser\MapParser;
use OpenTelemetry\SDK\Common\Configuration\Parser\RatioParser;
use OpenTelemetry\SDK\Common\Configuration\Resolver\CompositeResolver;
use OpenTelemetry\SDK\Common\Util\ClassConstantAccessor;
use UnexpectedValueException;

/**
 * Configuration can come from one or more of the following sources (from highest to lowest priority):
 * - values defined in php.ini
 * - environment variable ($_SERVER)
 * - configuration file (todo)
 */
class Configuration
{
    use LogsMessagesTrait;

    public static function has(string $name): bool
    {
        return CompositeResolver::instance()->hasVariable($name);
    }

    public static function getInt(string $key, ?int $default = null): int
    {
        return (int) self::validateVariableValue(
            CompositeResolver::instance()->resolve(
                self::validateVariableType($key, VariableTypes::INTEGER),
                $default
            ),
            FILTER_VALIDATE_INT
        );
    }

    public static function getString(string $key, ?string $default = null): string
    {
        return (string) self::validateVariableValue(
            CompositeResolver::instance()->resolve(
                self::validateVariableType($key, VariableTypes::STRING),
                $default
            )
        );
    }

    public static function getBoolean(string $key, ?bool $default = null): bool
    {
        $resolved = self::validateVariableValue(
            CompositeResolver::instance()->resolve(
                self::validateVariableType($key, VariableTypes::BOOL),
                null === $default ? $default : ($default ? 'true' : 'false')
            )
        );

        try {
            return BooleanParser::parse($resolved);
        } catch (InvalidArgumentException) {
            self::logWarning(sprintf('Invalid boolean value "%s" interpreted as "false" for %s', $resolved, $key));

            return false;
        }
    }

    public static function getMixed(string $key, $default = null)
    {
        return self::validateVariableValue(
            CompositeResolver::instance()->resolve(
                $key,
                $default
            )
        );
    }

    public static function getMap(string $key, ?array $default = null): array
    {
        return MapParser::parse(
            CompositeResolver::instance()->resolve(
                self::validateVariableType($key, VariableTypes::MAP),
                $default
            )
        );
    }

    public static function getList(string $key, ?array $default = null): array
    {
        return ListParser::parse(
            CompositeResolver::instance()->resolve(
                self::validateVariableType($key, VariableTypes::LIST),
                $default
            )
        );
    }

    public static function getEnum(string $key, ?string $default = null): string
    {
        return (string) self::validateVariableValue(
            CompositeResolver::instance()->resolve(
                self::validateVariableType($key, VariableTypes::ENUM),
                $default
            )
        );
    }

    public static function getFloat(string $key, ?float $default = null): float
    {
        return (float) self::validateVariableValue(
            CompositeResolver::instance()->resolve(
                self::validateVariableType($key, VariableTypes::FLOAT),
                $default
            ),
            FILTER_VALIDATE_FLOAT
        );
    }

    public static function getRatio(string $key, ?float $default = null): float
    {
        return RatioParser::parse(
            self::validateVariableValue(
                CompositeResolver::instance()->resolve(
                    self::validateVariableType($key, VariableTypes::RATIO),
                    $default
                )
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

    public static function getType(string $variableName): ?string
    {
        return ClassConstantAccessor::getValue(ValueTypes::class, $variableName);
    }

    public static function isEmpty($value): bool
    {
        // don't use 'empty()', since '0' is not considered to be empty
        return $value === null || $value === '';
    }

    private static function validateVariableType(string $variableName, string $type): string
    {
        $variableType = self::getType($variableName);

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
