<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration\Resolver;

use OpenTelemetry\SDK\Common\Configuration\Configuration;

/**
 * @internal
 */
class EnvironmentResolver implements ResolverInterface
{
    #[\Override]
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

    #[\Override]
    public function retrieveValue(string $variableName): mixed
    {
        $value = getenv($variableName);
        if ($value === false) {
            $value = $_SERVER[$variableName] ?? null;
        }

        return $value;
    }
}
