<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use Composer\InstalledVersions;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;

/**
 * @internal
 */
final readonly class ComposerPackageResource implements SelfCheckingResourceInterface
{

    public string $packageName;
    public string|false $version;

    public function __construct(string $packageName)
    {
        $this->packageName = $packageName;
        $this->version = self::getVersion($packageName);
    }

    public function isFresh(int $timestamp): bool
    {
        return $this->version === self::getVersion($this->packageName);
    }

    public function __toString(): string
    {
        return 'composer.' . $this->packageName;
    }

    /**
     * @psalm-suppress NullableReturnStatement,InvalidNullableReturnType
     */
    private static function getVersion(string $packageName): string|false
    {
        return InstalledVersions::isInstalled($packageName)
            ? InstalledVersions::getVersion($packageName)
            : false;
    }
}
