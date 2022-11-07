<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

use OpenTelemetry\SDK\Common\Configuration\Resolver\CompositeResolver;

/**
 * Configuration can come from one or more of the following sources (from highest to lowest priority):
 * - values defined in php.ini
 * - environment variable
 * - configuration file (todo)
 */
class Configuration
{
    public static function has(string $name): bool
    {
        return CompositeResolver::instance()->hasVariable($name);
    }

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
        return Accessor::getBool($key, null === $default ? null : ($default ? 'true' : 'false'));
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
}
