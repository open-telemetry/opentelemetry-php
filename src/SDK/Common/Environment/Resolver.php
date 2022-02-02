<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Environment;

use OpenTelemetry\SDK\Common\Util\ClassConstantAccessor;

class Resolver
{
    public static function resolveValue(string $variableName, $default = null): string
    {
        $value = self::getValue($variableName);

        if (self::isEmpty($value)) {
            return self::isEmpty($default) ? (string) self::getDefault($variableName) : (string) $default;
        }

        return (string) $value;
    }

    public static function getValue(string $variableName): ?string
    {
        $value = self::getRawValue($variableName);

        return $value ? trim($value) : $value;
    }

    public static function getRawValue(string $variableName): ?string
    {
        /** @psalm-suppress FalsableReturnStatement **/
        return self::hasVariable($variableName) ? getenv($variableName) : null;
    }

    public static function hasVariable(string $variableName): bool
    {
        return getenv($variableName) !== false;
    }

    public static function getDefault(string $variableName)
    {
        return ClassConstantAccessor::getValue(Defaults::class, $variableName);
    }

    public static function getType(string $variableName): ?string
    {
        return ClassConstantAccessor::getValue(ValueTypes::class, $variableName);
    }

    public static function getKnownValues(string $variableName): ?array
    {
        return ClassConstantAccessor::getValue(KnownValues::class, $variableName);
    }

    private static function isEmpty($value): bool
    {
        // don't use 'empty()', since '0' is not considered to be empty
        return $value === null || $value === '';
    }
}
