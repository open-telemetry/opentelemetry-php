<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration\Resolver;

use function is_string;
use Nevay\SPI\ServiceLoader;
use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceProvider;
use OpenTelemetry\Config\SDK\Configuration\Environment\LazyEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\PhpIniEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\ServerEnvSource;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use function trim;

/**
 * @internal
 */
// TODO raise to ^1.0 once sdk-configuration 1.0 is tagged; LazyEnvSource and EnvSourceProvider need >=0.2
#[PackageDependency('open-telemetry/sdk-configuration', '*')]
class SdkConfigurationResolver implements ResolverInterface
{
    /** @var list<EnvSource> */
    private readonly array $envSources;

    public function __construct()
    {
        $envSources = [];

        /** @var EnvSourceProvider $envSourceProvider */
        foreach (ServiceLoader::load(EnvSourceProvider::class) as $envSourceProvider) {
            $envSources[] = new LazyEnvSource($envSourceProvider->getEnvSource(...));
        }

        $envSources[] = new ServerEnvSource();
        $envSources[] = new PhpIniEnvSource();

        $this->envSources = $envSources;
    }

    #[\Override]
    public function retrieveValue(string $variableName): mixed
    {
        return $this->read($variableName);
    }

    #[\Override]
    public function hasVariable(string $variableName): bool
    {
        return !Configuration::isEmpty($this->read($variableName));
    }

    /**
     * Unlike {@see \OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader}, which reads values verbatim for declarative configuration,
     * this trims them and skips a blank value in favor of the next source.
     */
    private function read(string $variableName): ?string
    {
        foreach ($this->envSources as $envSource) {
            if (is_string($value = $envSource->readRaw($variableName)) && ($value = trim($value, " \t")) !== '') {
                return $value;
            }
        }

        return null;
    }
}
