<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Environment;

/**
 * Centralized methods for retrieving environment variables
 */
trait EnvironmentVariablesTrait
{
    /**
     * Retrieve an integer value from an environment variable
     */
    public function getIntFromEnvironment(string $key, int $default): int
    {
        return Accessor::getInt($key, (string) $default);
    }

    public function getFloatFromEnvironment(string $key, float $default): float
    {
        return Accessor::getFloat($key, (string) $default);
    }

    public function getStringFromEnvironment(string $key, string $default = ''): string
    {
        return Accessor::getString($key, $default);
    }

    public function getBooleanFromEnvironment(string $key, bool $default): bool
    {
        return Accessor::getBool($key, $default ? 'true' : 'false');
    }

    public function getMapFromEnvironment(string $key, string $default = null): array
    {
        return Accessor::getMap($key, $default);
    }

    public function getListFromEnvironment(string $key, string $default = null): array
    {
        return Accessor::getList($key, $default);
    }

    public function getEnumFromEnvironment(string $key, string $default = null): string
    {
        return Accessor::getEnum($key, $default);
    }

    public function getRatioFromEnvironment(string $key, float $default = null): float
    {
        return Accessor::getRatio($key, $default ? (string) $default : null);
    }

    public function hasEnvironmentVariable(string $key): bool
    {
        return Resolver::hasVariable($key);
    }
}
