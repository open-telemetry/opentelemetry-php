<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @template T
 *
 * @internal
 */
final class CompiledConfigurationFactory
{
    /**
     * @param ComponentProvider<T> $rootComponent
     * @param NodeInterface $node
     * @param iterable<ResourceTrackable> $resourceTrackable
     */
    public function __construct(
        private readonly ComponentProvider $rootComponent,
        private readonly NodeInterface $node,
        private readonly iterable $resourceTrackable,
    ) {
    }

    /**
     * @param array $configs configs to process
     * @param ResourceCollection|null $resources resources that can be used for cache invalidation
     * @throws InvalidConfigurationException if the configuration is invalid
     * @return ComponentPlugin<T> processed component plugin
     */
    public function process(array $configs, ?ResourceCollection $resources = null): ComponentPlugin
    {
        $resources?->addClassResource($this->rootComponent::class);
        foreach ($this->resourceTrackable as $trackable) {
            $trackable->trackResources($resources);
        }

        try {
            $properties = (new Processor())->process($this->node, $configs);
        } finally {
            foreach ($this->resourceTrackable as $trackable) {
                $trackable->trackResources(null);
            }
        }

        return new ComponentPlugin($properties, $this->rootComponent);
    }
}
