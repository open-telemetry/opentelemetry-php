<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration\Resolver;

use Nevay\SPI\ServiceLoader;
use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceProvider;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\LazyEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\PhpIniEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\ServerEnvSource;
use OpenTelemetry\SDK\Common\Configuration\Configuration;

/**
 * @internal
 */
#[PackageDependency('open-telemetry/sdk-configuration', '*')]
class SdkConfigurationResolver implements ResolverInterface
{
    private readonly EnvSourceReader $reader;

    public function __construct()
    {
        $envSources = [];

        /** @var EnvSourceProvider $envSourceProvider */
        foreach (ServiceLoader::load(EnvSourceProvider::class) as $envSourceProvider) {
            $envSources[] = new LazyEnvSource($envSourceProvider->getEnvSource(...));
        }

        $envSources[] = new ServerEnvSource();
        $envSources[] = new PhpIniEnvSource();

        $this->reader = new EnvSourceReader($envSources);
    }

    #[\Override]
    public function retrieveValue(string $variableName): mixed
    {
        return $this->reader->read($variableName);
    }

    #[\Override]
    public function hasVariable(string $variableName): bool
    {
        return !Configuration::isEmpty($this->reader->read($variableName));
    }
}
