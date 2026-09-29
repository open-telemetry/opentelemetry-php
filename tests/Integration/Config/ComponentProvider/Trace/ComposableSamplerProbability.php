<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<ComposableSamplerInterface>
 */
final class ComposableSamplerProbability implements ComponentProvider
{
    /**
     * @param array{
     *     ratio: float,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): ComposableSamplerInterface
    {
        return new NoopComposableSampler();
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('probability');
        $node
            ->children()
                ->floatNode('ratio')->min(0)->max(1)->defaultValue(1.0)->end()
            ->end()
        ;

        return $node;
    }
}
