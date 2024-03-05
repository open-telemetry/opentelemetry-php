<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\Accessor;

use LogicException;

class ClassConstantAccessor
{
    public static function requireValue(string $className, string $constantName): mixed
    {
        $constant = self::getFullName($className, $constantName);

        if (!defined($constant)) {
            throw new LogicException(
                sprintf('The class "%s" does not have a constant "%s"', $className, $constantName)
            );
        }

        return constant($constant);
    }

    public static function getValue(string $className, string $constantName): mixed
    {
        $constant = self::getFullName($className, $constantName);

        return defined($constant) ?  constant($constant) : null;
    }

    private static function getFullName(string $className, string $constantName): string
    {
        return $className . '::' . $constantName;
    }
}
