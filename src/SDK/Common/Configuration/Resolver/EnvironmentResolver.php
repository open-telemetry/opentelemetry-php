<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration\Resolver;

use OpenTelemetry\SDK\Common\Configuration\Resolver;

/**
 * @internal
 */
class EnvironmentResolver extends Resolver
{
    public function hasVariable(string $variableName): bool
    {
        return getenv($variableName) !== false || isset($_SERVER[$variableName]);
    }

    /**
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress InvalidReturnType
     */
    public function retrieveValue(string $variableName, ?string $default = ''): ?string
    {
        $value = getenv($variableName);
        if ($value === false) {
            $value = $_SERVER[$variableName] ?? $default;
        }
        if (is_array($value)) {
            return implode(',', $value);
        }

        return self::isEmpty($value) ? $default : (string) $value;
    }
}
