<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration;

/**
 * Minimal semantic version comparison helper for opentelemetry-configuration file_format values.
 *
 * Delegates to PHP's built-in version_compare(), which correctly handles pre-release
 * suffixes such as "1.0-rc.2" (treated as less than "1.0").
 *
 * @internal
 */
final class SemVer
{
    public static function gte(string $version, string $constraint): bool
    {
        return version_compare($version, $constraint, '>=');
    }

    public static function lt(string $version, string $constraint): bool
    {
        return version_compare($version, $constraint, '<');
    }

    public static function eq(string $version, string $constraint): bool
    {
        return version_compare($version, $constraint, '==');
    }
}
