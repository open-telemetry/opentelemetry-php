<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\Resolver;

use OpenTelemetry\Config\Configuration;

/**
 * @internal
 */
class EnvironmentResolver implements ResolverInterface
{
    public function hasVariable(string $variableName): bool
    {
        if (!Configuration::isEmpty($_SERVER[$variableName] ?? null)) {
            return true;
        }
        $env = getenv($variableName);
        if ($env === false) {
            return false;
        }

        return !Configuration::isEmpty($env);
    }

    /**
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress InvalidReturnType
     */
    public function retrieveValue(string $variableName)
    {
        $value = getenv($variableName);
        if ($value === false) {
            $value = $_SERVER[$variableName] ?? null;
        }

        return $value;
    }
}
