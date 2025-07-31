<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use OpenTelemetry\Config\SDK\Configuration\ResourceCollection;
use Symfony\Component\Config\Resource\ResourceInterface;

/**
 * @internal
 */
final class ConfigurationLoader implements \OpenTelemetry\Config\SDK\Configuration\Loader\ConfigurationLoader
{
    private array $configurations = [];
    private readonly ?ResourceCollection $resources;

    public function __construct(?ResourceCollection $resources)
    {
        $this->resources = $resources;
    }

    #[\Override]
    public function loadConfiguration(mixed $configuration): void
    {
        $this->configurations[] = $configuration;
    }

    #[\Override]
    public function addResource(ResourceInterface $resource): void
    {
        $this->resources?->addResource($resource);
    }

    public function getConfigurations(): array
    {
        return $this->configurations;
    }
}
