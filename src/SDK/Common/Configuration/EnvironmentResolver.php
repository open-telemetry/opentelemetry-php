<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

/**
 * @internal
 */
class EnvironmentResolver extends Resolver
{
    public function hasVariable(string $variableName): bool
    {
        return getenv($variableName) !== false || isset($_ENV[$variableName]);
    }

    /**
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress InvalidReturnType
     */
    public function retrieveValue(string $variableName, ?string $default = ''): ?string
    {
        $value = getenv($variableName);
        if ($value === false) {
            $value = $_ENV[$variableName] ?? $default;
        }

        return self::isEmpty($value) ? $default : $value;
    }
}
