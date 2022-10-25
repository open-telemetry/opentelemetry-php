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
    public static function getIntFromEnvironment(string $key, int $default): int
    {
        return Accessor::getInt($key, (string) $default);
    }

    public static function getStringFromEnvironment(string $key, string $default = ''): string
    {
        return Accessor::getString($key, $default);
    }

    public static function getBooleanFromEnvironment(string $key, bool $default = null): bool
    {
        return Accessor::getBool($key, is_null($default) ? null : ($default ? 'true' : 'false'));
    }

    public static function getMapFromEnvironment(string $key, string $default = null): array
    {
        return Accessor::getMap($key, $default);
    }

    public static function getListFromEnvironment(string $key, string $default = null): array
    {
        return Accessor::getList($key, $default);
    }

    public static function getEnumFromEnvironment(string $key, string $default = null): string
    {
        return Accessor::getEnum($key, $default);
    }

    public static function getRatioFromEnvironment(string $key, float $default = null): float
    {
        return Accessor::getRatio($key, $default ? (string) $default : null);
    }

    public static function hasEnvironmentVariable(string $key): bool
    {
        return Resolver::hasVariable($key);
    }
}
