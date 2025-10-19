<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config\ComponentProvider\Metrics;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<MetricExporterInterface>
 */
final class MetricExporterPrometheus implements ComponentProvider
{
    /**
     * @param array{
     *     host: string,
     *     port: int,
     *     without_units: bool,
     *     without_type_suffix: bool,
     *     without_scope_info: bool,
     *     with_resource_constant_labels: array{
     *         included: list<string>,
     *         excluded: list<string>,
     *     },
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): MetricExporterInterface
    {
        return new class() implements MetricExporterInterface {
            #[\Override]
            public function export(iterable $batch): bool
            {
                return true;
            }

            #[\Override]
            public function shutdown(): bool
            {
                return true;
            }
        };
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('prometheus/development');
        $node
            ->children()
                ->scalarNode('host')->isRequired()->validate()->always(Validation::ensureString())->end()->end()
                ->scalarNode('port')->isRequired()->validate()->always(Validation::ensureNumber())->end()->end()
                ->booleanNode('without_units')->defaultFalse()->end()
                ->booleanNode('without_type_suffix')->defaultFalse()->end()
                ->booleanNode('without_scope_info')->defaultFalse()->end()
                ->booleanNode('without_scope_trace')->defaultFalse()->end()
                ->arrayNode('with_resource_constant_labels')
                    ->children()
                        ->arrayNode('included')
                            ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                        ->end()
                        ->arrayNode('excluded')
                            ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('translation_strategy')->defaultValue('UnderscoreEscapingWithSuffixes')->end()
            ->end()
        ;

        return $node;
    }
}
