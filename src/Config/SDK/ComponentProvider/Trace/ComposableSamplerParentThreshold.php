<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSampler;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @experimental
 * @implements ComponentProvider<ComposableInterface>
 */
final class ComposableSamplerParentThreshold implements ComponentProvider
{
    /**
     * @param array{
     *     root: ComponentPlugin<ComposableInterface>,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): ComposableInterface
    {
        return ConsistentSampler::parentBased($properties['root']->create($context));
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('parent_threshold');
        $node
            ->children()
                ->append($registry->component('root', ComposableInterface::class)->isRequired())
            ->end()
        ;

        return $node;
    }
}
