<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableProbability;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\CompositeSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Consistent probability sampler.
 *
 * @internal
 * @experimental
 * @implements ComponentProvider<SamplerInterface>
 */
final class SamplerProbability implements ComponentProvider
{
    /**
     * @param array{
     *     ratio: float,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): SamplerInterface
    {
        return new CompositeSampler(new ComposableProbability($properties['ratio']));
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('probability/development');
        $node
            ->children()
                ->floatNode('ratio')->min(0)->max(1)->defaultValue(1.0)->end()
            ->end()
        ;

        return $node;
    }
}
