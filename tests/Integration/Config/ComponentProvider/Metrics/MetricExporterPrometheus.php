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
     *     without_scope_info: bool,
     *     "without_target_info/development": bool,
     *     with_resource_constant_labels: array{
     *         included: list<string>,
     *         excluded: list<string>,
     *     },
     *     translation_strategy: string,
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
                ->scalarNode('host')->defaultValue('localhost')->validate()->always(Validation::ensureString())->end()->end()
                ->scalarNode('port')->defaultValue(9464)->validate()->always(Validation::ensureNumber())->end()->end()
                ->booleanNode('without_scope_info')->defaultFalse()->end()
                ->booleanNode('without_target_info/development')->defaultFalse()->end()
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
                ->enumNode('translation_strategy')
                    ->defaultValue('underscore_escaping_with_suffixes')
                    ->values([
                        'underscore_escaping_with_suffixes',
                        'underscore_escaping_without_suffixes/development',
                        'no_utf8_escaping_with_suffixes/development',
                        'no_translation/development',
                    ])
                ->end()
            ->end()
        ;

        return $node;
    }
}
