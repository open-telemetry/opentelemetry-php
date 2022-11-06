<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

use OpenTelemetry\SDK\Common\Util\ClassConstantAccessor;

abstract class Resolver
{
    abstract public function retrieveValue(string $variableName, ?string $default = ''): ?string;

    abstract public function hasVariable(string $variableName): bool;

    public function resolveValue(string $variableName, $default = null): string
    {
        $value = $this->getValue($variableName);

        if (self::isEmpty($value)) {
            return self::isEmpty($default) ? (string) self::getDefault($variableName) : (string) $default;
        }

        return (string) $value;
    }

    public function getValue(string $variableName): ?string
    {
        $value = $this->getRawValue($variableName);

        return $value ? trim($value) : $value;
    }

    public function getRawValue(string $variableName): ?string
    {
        /** @psalm-suppress FalsableReturnStatement **/
        return $this->hasVariable($variableName) ? $this->retrieveValue($variableName) : null;
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

    protected static function isEmpty($value): bool
    {
        // don't use 'empty()', since '0' is not considered to be empty
        return $value === null || $value === '';
    }
}
