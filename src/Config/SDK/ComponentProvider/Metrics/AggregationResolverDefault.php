<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderTrait;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @implements ComponentProvider<DefaultAggregationProviderInterface>
 */
final class AggregationResolverDefault implements ComponentProvider
{

    /**
     * @param array{} $properties
     */
    public function createPlugin(array $properties, Context $context): DefaultAggregationProviderInterface
    {
        // TODO Implement proper aggregation providers (default, drop, explicit_bucket_histogram, last_value, sum) to handle advisory
        return new class() implements DefaultAggregationProviderInterface {
            use DefaultAggregationProviderTrait;
        };
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        return new ArrayNodeDefinition('default');
    }
}
