<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Environment\Adapter;

use function array_diff_key;
use Composer\InstalledVersions;
use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\Config\SDK\Configuration\Environment\ArrayEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceProvider;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\PathException;

#[PackageDependency('symfony/dotenv', '^5.4 || ^6.4 || ^7.0')]
final class SymfonyDotenvProvider implements EnvSourceProvider
{
    public function getEnvSource(): EnvSource
    {
        $installPath = InstalledVersions::getRootPackage()['install_path'];

        $backup = [$_SERVER, $_ENV];
        $env = [];

        try {
            (new Dotenv())->bootEnv($installPath . '/.env');
            $env = $_SERVER;
        } catch (PathException) {
        } finally {
            [$_SERVER, $_ENV] = $backup;
        }

        return new ArrayEnvSource(array_diff_key($env, $_SERVER));
    }
}
