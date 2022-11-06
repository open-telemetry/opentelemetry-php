<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

use OpenTelemetry\SDK\Common\Configuration\Parser\BooleanParser;
use OpenTelemetry\SDK\Common\Configuration\Parser\ListParser;
use OpenTelemetry\SDK\Common\Configuration\Parser\MapParser;
use OpenTelemetry\SDK\Common\Configuration\Parser\RatioParser;
use UnexpectedValueException;

class Accessor
{
    public static function has(Resolver $resolver, string $variableName): bool
    {
        return $resolver->hasVariable($variableName);
    }

    public static function getString(Resolver $resolver, string $variableName, string $default = null): string
    {
        return (string) self::validateVariableValue(
            $resolver->resolveValue(
                self::validateVariableType($variableName, VariableTypes::STRING),
                $default
            )
        );
    }

    public static function getBool(Resolver $resolver, string $variableName, string $default = null): bool
    {
        return BooleanParser::parse(
            self::validateVariableValue(
                $resolver->resolveValue(
                    self::validateVariableType($variableName, VariableTypes::BOOL),
                    $default
                )
            )
        );
    }

    public static function getInt(Resolver $resolver, string $variableName, string $default = null): int
    {
        return (int) self::validateVariableValue(
            $resolver->resolveValue(
                self::validateVariableType($variableName, VariableTypes::INTEGER),
                $default
            ),
            FILTER_VALIDATE_INT
        );
    }

    public static function getFloat(Resolver $resolver, string $variableName, string $default = null): float
    {
        return (float) self::validateVariableValue(
            $resolver->resolveValue(
                self::validateVariableType($variableName, VariableTypes::FLOAT),
                $default
            ),
            FILTER_VALIDATE_FLOAT
        );
    }

    public static function getRatio(Resolver $resolver, string $variableName, string $default = null): float
    {
        return RatioParser::parse(
            self::validateVariableValue(
                $resolver->resolveValue(
                    self::validateVariableType($variableName, VariableTypes::RATIO),
                    $default
                )
            )
        );
    }

    public static function getEnum(Resolver $resolver, string $variableName, string $default = null): string
    {
        return (string) self::validateVariableValue(
            $resolver->resolveValue(
                self::validateVariableType($variableName, VariableTypes::ENUM),
                $default
            )
        );
    }

    public static function getList(Resolver $resolver, string $variableName, string $default = null): array
    {
        return ListParser::parse(
            $resolver->resolveValue(
                self::validateVariableType($variableName, VariableTypes::LIST),
                $default
            )
        );
    }

    public static function getMap(Resolver $resolver, string $variableName, string $default = null): array
    {
        return MapParser::parse(
            $resolver->resolveValue(
                self::validateVariableType($variableName, VariableTypes::MAP),
                $default
            )
        );
    }

    public static function getMixed(Resolver $resolver, string $variableName, string $default = null)
    {
        return self::validateVariableValue(
            $resolver->resolveValue(
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
