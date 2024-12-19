<?php

declare(strict_types=1);

namespace ExampleSDK\ComponentProvider\Trace;

use BadMethodCallException;
use ExampleSDK\Trace\Sampler;
use OpenTelemetry\Config\SDK\Configuration\ComponentPlugin;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class SamplerParentBased implements ComponentProvider
{

    /**
     * @param array{
     *     root: ComponentPlugin<Sampler>,
     *     remote_parent_sampled: ?ComponentPlugin<Sampler>,
     *     remote_parent_not_sampled: ?ComponentPlugin<Sampler>,
     *     local_parent_sampled: ?ComponentPlugin<Sampler>,
     *     local_parent_not_sampled: ?ComponentPlugin<Sampler>,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): Sampler
    {
        throw new BadMethodCallException('not implemented');
    }

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('parent_based');
        $node
            ->children()
                ->append($registry->component('root', Sampler::class)->isRequired())
                ->append($registry->component('remote_parent_sampled', Sampler::class))
                ->append($registry->component('remote_parent_not_sampled', Sampler::class))
                ->append($registry->component('local_parent_sampled', Sampler::class))
                ->append($registry->component('local_parent_not_sampled', Sampler::class))
            ->end()
        ;

        return $node;
    }
}
