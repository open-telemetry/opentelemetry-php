<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config\ComponentProvider\Metrics;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderTrait;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<DefaultAggregationProviderInterface>
 */
final class AggregationResolverExplicitBucketHistogram implements ComponentProvider
{
    /**
     * @param array{
     *      boundaries: array<int|float>,
     *      record_min_max: bool,
     *  } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): DefaultAggregationProviderInterface
    {
        return new class() implements DefaultAggregationProviderInterface {
            use DefaultAggregationProviderTrait;
        };
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('explicit_bucket_histogram');
        $node
            ->children()
                ->arrayNode('boundaries')
                    ->prototype('scalar')
                        ->validate()->always(Validation::ensureNumber())->end()
                    ->end()
                ->end()
                ->booleanNode('record_min_max')->defaultTrue()->end()
            ->end();

        return $node;
    }
}
