<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

/**
 * Configuration can come from one or more of the following sources (from highest to lowest priority):
 * - values defined in php.ini
 * - environment variable
 * - configuration file (todo)
 */
class Configuration
{
    private static ?array $resolvers = null;

    /**
     * @return array<Resolver>
     */
    private static function resolvers(): array
    {
        self::$resolvers ??= [
            new IniResolver(),
            new EnvironmentResolver(),
        ];

        return self::$resolvers;
    }

    public static function has(string $name): bool
    {
        foreach (self::resolvers() as $resolver) {
            if ($resolver->hasVariable($name)) {
                return true;
            }
        }

        return false;
    }

    public static function getResolver(string $name): Resolver
    {
        foreach (self::resolvers() as $resolver) {
            if ($resolver->hasVariable($name)) {
                return $resolver;
            }
        }

        return DefaultResolver::instance();
    }

    public static function getInt(string $key, int $default): int
    {
        $resolver = self::getResolver($key);

        return Accessor::getInt($resolver, $key, (string) $default);
    }

    public static function getString(string $key, string $default = ''): string
    {
        $resolver = self::getResolver($key);

        return Accessor::getString($resolver, $key, $default);
    }

    public static function getBoolean(string $key, bool $default = null): bool
    {
        $resolver = self::getResolver($key);

        return Accessor::getBool($resolver, $key, null === $default ? null : ($default ? 'true' : 'false'));
    }

    public static function getMap(string $key, string $default = null): array
    {
        $resolver = self::getResolver($key);

        return Accessor::getMap($resolver, $key, $default);
    }

    public static function getList(string $key, string $default = null): array
    {
        $resolver = self::getResolver($key);

        return Accessor::getList($resolver, $key, $default);
    }

    public static function getEnum(string $key, string $default = null): string
    {
        $resolver = self::getResolver($key);

        return Accessor::getEnum($resolver, $key, $default);
    }

    public static function getRatio(string $key, float $default = null): float
    {
        $resolver = self::getResolver($key);

        return Accessor::getRatio($resolver, $key, $default ? (string) $default : null);
    }
}
