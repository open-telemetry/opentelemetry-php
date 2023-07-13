<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common;

use OpenTelemetry\API\Configuration\ClassConstantAccessor;
use OpenTelemetry\API\Configuration\VariableTypes;
use OpenTelemetry\SDK\Common\Configuration\Defaults;
use OpenTelemetry\SDK\Common\Configuration\ValueTypes;
use UnexpectedValueException;

/**
 * @psalm-internal \OpenTelemetry
 */
class Configuration extends \OpenTelemetry\API\Configuration
{
    /**
     * @override
     */
    public static function getDefault(string $key, $default = null)
    {
        if ($default !== null) {
            return $default;
        }

        return ClassConstantAccessor::getValue(Defaults::class, $key);
    }

    /**
     * @override
     */
    protected static function validateVariableType(string $variableName, string $type): string
    {
        $variableType = self::getType($variableName);

        if ($variableType !== null && $variableType !== $type && $variableType !== VariableTypes::MIXED) {
            throw new UnexpectedValueException(
                sprintf('Variable "%s" is not supposed to be of type "%s" but type "%s"', $variableName, $type, $variableType)
            );
        }

        return $variableName;
    }

    /**
     * @override
     */
    public static function getType(string $variableName): ?string
    {
        return ClassConstantAccessor::getValue(ValueTypes::class, $variableName);
    }
}
