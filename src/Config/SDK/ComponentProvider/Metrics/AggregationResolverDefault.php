<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use Nevay\OTelSDK\Configuration\ComponentProvider;
use Nevay\OTelSDK\Configuration\ComponentProviderRegistry;
use Nevay\OTelSDK\Configuration\Context;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderTrait;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

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
