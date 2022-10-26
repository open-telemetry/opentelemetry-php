<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Environment;

/**
 * Centralized methods for retrieving environment variables
 */
class EnvironmentVariables
{
    /**
     * Retrieve an integer value from an environment variable
     */
    public static function getInt(string $key, int $default): int
    {
        return Accessor::getInt($key, (string) $default);
    }

    public static function getString(string $key, string $default = ''): string
    {
        return Accessor::getString($key, $default);
    }

    public static function getBoolean(string $key, bool $default = null): bool
    {
        return Accessor::getBool($key, is_null($default) ? null : ($default ? 'true' : 'false'));
    }

    public static function getMap(string $key, string $default = null): array
    {
        return Accessor::getMap($key, $default);
    }

    public static function getList(string $key, string $default = null): array
    {
        return Accessor::getList($key, $default);
    }

    public static function getEnum(string $key, string $default = null): string
    {
        return Accessor::getEnum($key, $default);
    }

    public static function getRatio(string $key, float $default = null): float
    {
        return Accessor::getRatio($key, $default ? (string) $default : null);
    }

    public static function has(string $key): bool
    {
        return Resolver::hasVariable($key);
    }
}
