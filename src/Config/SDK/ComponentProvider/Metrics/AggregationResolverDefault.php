<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderTrait;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<DefaultAggregationProviderInterface>
 */
final class AggregationResolverDefault implements ComponentProvider
{
    /**
     * @param array{} $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): DefaultAggregationProviderInterface
    {
        // TODO Implement proper aggregation providers (default, drop, explicit_bucket_histogram, last_value, sum) to handle advisory
        return new class() implements DefaultAggregationProviderInterface {
            use DefaultAggregationProviderTrait;
        };
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('default');
    }
}
