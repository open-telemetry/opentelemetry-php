<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Environment\Adapter;

use Composer\InstalledVersions;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\Config\SDK\Configuration\Environment\ArrayEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceProvider;

#[PackageDependency('vlucas/phpdotenv', '^4.0 || ^5.0')]
final class VlucasPhpdotenvProvider implements EnvSourceProvider
{
    /** @psalm-suppress UndefinedClass */
    #[\Override]
    public function getEnvSource(): EnvSource
    {
        $backup = [$_SERVER, $_ENV];
        $env = [];

        try {
            $env = Dotenv::createImmutable([InstalledVersions::getRootPackage()['install_path']])->load();
        } catch (InvalidPathException) {
        } finally {
            [$_SERVER, $_ENV] = $backup;
        }

        return new ArrayEnvSource(array_diff_key($env, $_SERVER));
    }
}
