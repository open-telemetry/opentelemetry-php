<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

use OpenTelemetry\SDK\Common\Configuration\Parser\BooleanParser;
use OpenTelemetry\SDK\Common\Configuration\Parser\ListParser;
use OpenTelemetry\SDK\Common\Configuration\Parser\MapParser;
use OpenTelemetry\SDK\Common\Configuration\Parser\RatioParser;
use OpenTelemetry\SDK\Common\Configuration\Resolver\CompositeResolver;
use UnexpectedValueException;

class Accessor
{
    public static function has(string $variableName): bool
    {
        return CompositeResolver::instance()->hasVariable($variableName);
    }

    public static function getString(string $variableName, string $default = null): string
    {
        return (string) self::validateVariableValue(
            CompositeResolver::instance()->resolveValue(
                self::validateVariableType($variableName, VariableTypes::STRING),
                $default
            )
        );
    }

    public static function getBool(string $variableName, string $default = null): bool
    {
        return BooleanParser::parse(
            self::validateVariableValue(
                CompositeResolver::instance()->resolveValue(
                    self::validateVariableType($variableName, VariableTypes::BOOL),
                    $default
                )
            )
        );
    }

    public static function getInt(string $variableName, string $default = null): int
    {
        return (int) self::validateVariableValue(
            CompositeResolver::instance()->resolveValue(
                self::validateVariableType($variableName, VariableTypes::INTEGER),
                $default
            ),
            FILTER_VALIDATE_INT
        );
    }

    public static function getFloat(string $variableName, string $default = null): float
    {
        return (float) self::validateVariableValue(
            CompositeResolver::instance()->resolveValue(
                self::validateVariableType($variableName, VariableTypes::FLOAT),
                $default
            ),
            FILTER_VALIDATE_FLOAT
        );
    }

    public static function getRatio(string $variableName, string $default = null): float
    {
        return RatioParser::parse(
            self::validateVariableValue(
                CompositeResolver::instance()->resolveValue(
                    self::validateVariableType($variableName, VariableTypes::RATIO),
                    $default
                )
            )
        );
    }

    public static function getEnum(string $variableName, string $default = null): string
    {
        return (string) self::validateVariableValue(
            CompositeResolver::instance()->resolveValue(
                self::validateVariableType($variableName, VariableTypes::ENUM),
                $default
            )
        );
    }

    public static function getList(string $variableName, string $default = null): array
    {
        return ListParser::parse(
            CompositeResolver::instance()->resolveValue(
                self::validateVariableType($variableName, VariableTypes::LIST),
                $default
            )
        );
    }

    public static function getMap(string $variableName, string $default = null): array
    {
        return MapParser::parse(
            CompositeResolver::instance()->resolveValue(
                self::validateVariableType($variableName, VariableTypes::MAP),
                $default
            )
        );
    }

    public static function getMixed(string $variableName, string $default = null)
    {
        return self::validateVariableValue(
            CompositeResolver::instance()->resolveValue(
                $variableName,
                $default
            )
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
