<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Metrics\Aggregation\DropAggregation;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @internal
 * @implements ComponentProvider<DefaultAggregationProviderInterface>
 */
final class AggregationResolverDrop implements ComponentProvider
{
    /**
     * @param array{} $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): DefaultAggregationProviderInterface
    {
        return new class() implements DefaultAggregationProviderInterface {
            #[\Override]
            public function defaultAggregation($instrumentType, array $advisory = []): AggregationInterface
            {
                return new DropAggregation();
            }
        };
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('drop');
    }
}
