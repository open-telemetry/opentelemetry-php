<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvResource;
use OpenTelemetry\Config\SDK\Configuration\ResourceCollection;

/**
 * @internal
 */
final class TrackingEnvReader implements EnvReader, ResourceTrackable
{
    private readonly EnvReader $envReader;
    private ?ResourceCollection $resources = null;

    public function __construct(EnvReader $envReader)
    {
        $this->envReader = $envReader;
    }

    public function trackResources(?ResourceCollection $resources): void
    {
        $this->resources = $resources;
    }

    public function read(string $name): ?string
    {
        $value = $this->envReader->read($name);
        $this->resources?->addResource(new EnvResource($name, $value));

        return $value;
    }
}
