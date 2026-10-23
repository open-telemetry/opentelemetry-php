<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * The SDK has no exponential histogram. The settings are validated so a schema-valid file still
 * parses, and the view falls back to the metric reader's default aggregation.
 *
 * @implements ComponentProvider<DefaultAggregationProviderInterface>
 */
final class AggregationResolverBase2ExponentialBucketHistogram implements ComponentProvider
{
    /**
     * @param array{
     *     max_scale: int,
     *     max_size: int,
     *     record_min_max: bool,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): DefaultAggregationProviderInterface
    {
        $context->logger->log(
            LogLevel::WARNING,
            'View aggregation "base2_exponential_bucket_histogram" is not supported, using the metric reader default',
        );

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
        $node = $builder->arrayNode('base2_exponential_bucket_histogram');
        $node
            ->children()
                ->integerNode('max_scale')->min(-10)->max(20)->defaultValue(20)->end()
                ->integerNode('max_size')->min(2)->defaultValue(160)->end()
                ->booleanNode('record_min_max')->defaultTrue()->end()
            ->end();

        return $node;
    }
}
