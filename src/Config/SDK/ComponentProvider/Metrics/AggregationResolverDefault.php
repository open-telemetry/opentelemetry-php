<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<DefaultAggregationProviderInterface>
 */
final class AggregationResolverDefault implements ComponentProvider
{
    /**
     * Resolves to null, so the metric reader's default aggregation applies.
     *
     * @param array{} $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): DefaultAggregationProviderInterface
    {
        return new class() implements DefaultAggregationProviderInterface {
            #[\Override]
            public function defaultAggregation($instrumentType, array $advisory = []): ?AggregationInterface
            {
                return null;
            }
        };
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('default');
    }
}
