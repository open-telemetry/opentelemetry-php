<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Environment;

use OpenTelemetry\SDK\Common\Util\ClassConstantAccessor;

class Resolver
{
    public static function resolveValue(string $variableName, $default = null): ?string
    {
        $value = self::getValue($variableName);

        if ($value === null) {
            return $default === null ? (string) self::getDefault($variableName) : $default;
        }

        return $value;
    }

    public static function getValue(string $variableName): ?string
    {
        $value = self::getRawValue($variableName);

        return $value ? trim($value) : $value;
    }

    public static function getRawValue(string $variableName): ?string
    {
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

    public static function getType(string $variableName)
    {
        return ClassConstantAccessor::getValue(ValueTypes::class, $variableName);
    }

    public static function getKnownValues(string $variableName): ?array
    {
        return ClassConstantAccessor::getValue(KnownValues::class, $variableName);
    }
}
