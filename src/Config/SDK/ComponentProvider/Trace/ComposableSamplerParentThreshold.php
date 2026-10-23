<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableParentThreshold;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableSamplerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @experimental
 * @implements ComponentProvider<ComposableSamplerInterface>
 */
final class ComposableSamplerParentThreshold implements ComponentProvider
{
    /**
     * @param array{
     *     root: ComponentPlugin<ComposableSamplerInterface>,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): ComposableSamplerInterface
    {
        return new ComposableParentThreshold($properties['root']->create($context));
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('parent_threshold');
        $node
            ->children()
                ->append($registry->component('root', ComposableSamplerInterface::class)->isRequired())
            ->end()
        ;

        return $node;
    }
}
