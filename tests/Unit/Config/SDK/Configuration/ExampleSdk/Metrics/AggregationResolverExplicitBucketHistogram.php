<?php

declare(strict_types=1);

namespace ExampleSDK\ComponentProvider\Metrics;

use BadMethodCallException;
use ExampleSDK\Metrics\AggregationResolver;
use InvalidArgumentException;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class AggregationResolverExplicitBucketHistogram implements ComponentProvider
{

    /**
     * @param array{
     *     boundaries: list<float|int>,
     *     record_min_max: bool,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): AggregationResolver
    {
        throw new BadMethodCallException('not implemented');
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('explicit_bucket_histogram');
        $node
            ->children()
                ->arrayNode('boundaries')
                    ->floatPrototype()->end()
                    ->validate()
                        ->ifArray()
                        ->then(static function (array $boundaries): array {
                            $last = -INF;
                            foreach ($boundaries as $boundary) {
                                if ($boundary <= $last) {
                                    throw new InvalidArgumentException('histogram boundaries must be strictly ascending');
                                }

                                $last = $boundary;
                            }

                            return $boundaries;
                        })
                    ->end()
                ->end()
                ->booleanNode('record_min_max')->defaultTrue()->end()
            ->end()
        ;

        return $node;
    }
}
