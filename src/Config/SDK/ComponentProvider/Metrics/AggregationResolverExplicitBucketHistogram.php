<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\SDK\Metrics\Aggregation\ExplicitBucketHistogramAggregation;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<DefaultAggregationProviderInterface>
 */
final class AggregationResolverExplicitBucketHistogram implements ComponentProvider
{
    /**
     * Boundaries come from the view, else the instrument's advisory, else the SDK default.
     * An empty list is a single bucket, not "use the default".
     *
     * @param array{
     *     boundaries: list<int|float>|null,
     *     record_min_max: bool,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): DefaultAggregationProviderInterface
    {
        return new class($properties['boundaries'], $properties['record_min_max']) implements DefaultAggregationProviderInterface {
            /** Same as {@see \OpenTelemetry\SDK\Metrics\DefaultAggregationProviderTrait} */
            private const DEFAULT_BOUNDARIES = [0, 5, 10, 25, 50, 75, 100, 250, 500, 1000];

            /**
             * @param list<int|float>|null $boundaries
             */
            public function __construct(
                private readonly ?array $boundaries,
                private readonly bool $recordMinMax,
            ) {
            }

            #[\Override]
            public function defaultAggregation($instrumentType, array $advisory = []): AggregationInterface
            {
                return new ExplicitBucketHistogramAggregation(
                    $this->boundaries ?? $advisory['ExplicitBucketBoundaries'] ?? self::DEFAULT_BOUNDARIES,
                    $this->recordMinMax,
                );
            }
        };
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('explicit_bucket_histogram');
        $node
            ->children()
                ->arrayNode('boundaries')
                    ->defaultNull()
                    ->prototype('scalar')
                        ->validate()->always(Validation::ensureNumber())->end()
                    ->end()
                ->end()
                ->booleanNode('record_min_max')->defaultTrue()->end()
            ->end();

        return $node;
    }
}
