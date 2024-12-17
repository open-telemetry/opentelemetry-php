<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config\ComponentProvider\Metrics;

use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderTrait;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

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
    public function createPlugin(array $properties, Context $context): DefaultAggregationProviderInterface
    {
        return new class() implements DefaultAggregationProviderInterface {
            use DefaultAggregationProviderTrait;
        };
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('explicit_bucket_histogram');
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
