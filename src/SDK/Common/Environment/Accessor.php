<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Environment;

use OpenTelemetry\SDK\Common\Environment\Parser\BooleanParser;
use OpenTelemetry\SDK\Common\Environment\Parser\ListParser;
use OpenTelemetry\SDK\Common\Environment\Parser\MapParser;
use OpenTelemetry\SDK\Common\Environment\Parser\RatioParser;
use UnexpectedValueException;

class Accessor
{
    public static function getString(string $variableName, string $default = null): string
    {
        return (string) self::validateVariableValue(
            Resolver::resolveValue(
                self::validateVariableType($variableName, VariableTypes::STRING),
                $default
            )
        );
    }

    public static function getBool(string $variableName, string $default = null): bool
    {
        return BooleanParser::parse(
            self::validateVariableValue(
                Resolver::resolveValue(
                    self::validateVariableType($variableName, VariableTypes::BOOL),
                    $default
                )
            )
        );
    }

    public static function getInt(string $variableName, string $default = null): int
    {
        return (int) self::validateVariableValue(
            Resolver::resolveValue(
                self::validateVariableType($variableName, VariableTypes::INTEGER),
                $default
            ),
            FILTER_VALIDATE_INT
        );
    }

    public static function getRatio(string $variableName, string $default = null): float
    {
        return RatioParser::parse(
            self::validateVariableValue(
                Resolver::resolveValue(
                    self::validateVariableType($variableName, VariableTypes::RATIO),
                    $default
                )
            )
        );
    }

    public static function getEnum(string $variableName, string $default = null): string
    {
        return (string) self::validateVariableValue(
            Resolver::resolveValue(
                self::validateVariableType($variableName, VariableTypes::ENUM),
                $default
            )
        );
    }

    public static function getList(string $variableName, string $default = null): array
    {
        return ListParser::parse(
            Resolver::resolveValue(
                self::validateVariableType($variableName, VariableTypes::LIST),
                $default
            )
        );
    }

    public static function getMap(string $variableName, string $default = null): array
    {
        return MapParser::parse(
            Resolver::resolveValue(
                self::validateVariableType($variableName, VariableTypes::MAP),
                $default
            )
        );
    }

    public static function getMixed(string $variableName, string $default = null, ?string $type = null)
    {
        return self::validateVariableValue(
            Resolver::resolveValue(
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
