<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use function array_is_list;
use function count;
use InvalidArgumentException;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Metrics\Aggregation\ExplicitBucketHistogramAggregation;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @internal
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
                    ->floatPrototype()->end()
                    ->validate()->always(static function (?array $boundaries): ?array {
                        if ($boundaries === null) {
                            return null;
                        }
                        if (!array_is_list($boundaries)) {
                            throw new InvalidArgumentException('must be a list');
                        }
                        for ($i = 1, $n = count($boundaries); $i < $n; $i++) {
                            if ($boundaries[$i] <= $boundaries[$i - 1]) {
                                throw new InvalidArgumentException('must be strictly ascending');
                            }
                        }

                        return $boundaries;
                    })->end()
                ->end()
                ->booleanNode('record_min_max')->defaultTrue()->end()
            ->end();

        return $node;
    }
}
