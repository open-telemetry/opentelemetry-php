<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Adapter\DotEnv;

use Composer\InstalledVersions;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\SDK\Common\Configuration\Resolver\ResolverInterface;
use function str_starts_with;

#[PackageDependency('vlucas/phpdotenv', '^5.0')]
final class VlucasPhpdotenvResolver implements ResolverInterface
{
    public function retrieveValue(string $variableName)
    {
        return $this->dotEnvValues()[$variableName] ?? null;
    }

    public function hasVariable(string $variableName): bool
    {
        return array_key_exists($variableName, $this->dotEnvValues());
    }

    private function dotEnvValues(): array
    {
        static $env;

        try {
            $env ??= array_filter(
                Dotenv::createArrayBacked([InstalledVersions::getRootPackage()['install_path']])->safeLoad(),
                // Discard any environment variables that do not start with OTEL_
                fn (string $name) => str_starts_with($name, 'OTEL_'),
                ARRAY_FILTER_USE_KEY,
            );
        } catch (InvalidFileException) {
            $env = [];
        }

        return $env;
    }
}
