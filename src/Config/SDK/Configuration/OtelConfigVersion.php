<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration;

/**
 * Represents a supported opentelemetry-configuration file_format version.
 *
 * Use {@see self::fromVersion()} to resolve a raw file_format string (which may include
 * a patch component or a pre-release suffix) to a known version constant.
 *
 * @internal
 */
enum OtelConfigVersion: string
{
    case V1_0 = '1.0';
    case V1_1 = '1.1';

    /**
     * Resolves a raw file_format string to a supported {@see OtelConfigVersion}.
     *
     * Accepts:
     *  - Stable versions with optional patch: "1.0", "1.0.0", "1.1", "1.1.3", …
     *  - Pre-release versions: "1.0-rc.2", "1.1-rc.1", … (accepted with a deprecation notice)
     *
     * Throws {@see \InvalidArgumentException} for unsupported major/minor combinations.
     *
     * @throws \InvalidArgumentException when the version is not supported
     */
    public static function fromVersion(string $version): self
    {
        $isPreRelease = str_contains($version, '-');
        $base = $isPreRelease ? (string) preg_replace('/-.*$/', '', $version) : $version;

        // Normalise to "major.minor" so that patch versions ("1.0.5") also resolve.
        $parts = explode('.', $base);
        $normalized = $parts[0] . '.' . ($parts[1] ?? '0');

        $case = self::tryFrom($normalized);
        if ($case === null) {
            $supported = implode(' and ', array_map(
                static fn (self $v) => '"' . $v->value . '"',
                self::cases(),
            ));

            throw new \InvalidArgumentException(sprintf(
                'Unsupported file_format "%s"; supported versions are %s.',
                $version,
                $supported,
            ));
        }

        if ($isPreRelease) {
            trigger_error(sprintf(
                'file_format "%s" is a pre-release version and is deprecated; use "%s" or "%s" instead.',
                $version,
                self::V1_0->value,
                self::V1_1->value,
            ), \E_USER_DEPRECATED);
        }

        return $case;
    }
}
