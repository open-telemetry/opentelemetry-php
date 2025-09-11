<?php

declare(strict_types=1);

namespace ExampleSDK\ComponentProvider;

use BadMethodCallException;
use ExampleSDK\Configuration;
use ExampleSDK\Logs\LogRecordProcessor;
use ExampleSDK\Metrics\AggregationResolver;
use ExampleSDK\Metrics\MetricReader;
use ExampleSDK\Trace\Sampler;
use ExampleSDK\Trace\SpanProcessor;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class OpenTelemetryConfiguration implements ComponentProvider
{
    /**
     * @param array{
     *     file_format: '0.1',
     *     disabled: bool,
     *     resource: array{
     *         attributes: array,
     *         schema_url: ?string,
     *     },
     *     attribute_limits: array{
     *         attribute_value_length_limit: ?int<0, max>,
     *         attribute_count_limit: int<0, max>,
     *     },
     *     propagator: ?ComponentPlugin<TextMapPropagatorInterface>,
     *     response_propagator/development: ?ComponentPlugin<ResponsePropagatorInterface>,
     *     tracer_provider: array{
     *         limits: array{
     *             attribute_value_length_limit: ?int<0, max>,
     *             attribute_count_limit: ?int<0, max>,
     *             event_count_limit: int<0, max>,
     *             link_count_limit: int<0, max>,
     *             event_attribute_count_limit: ?int<0, max>,
     *             link_attribute_count_limit: ?int<0, max>,
     *         },
     *         sampler: ?ComponentPlugin<Sampler>,
     *         processors: list<ComponentPlugin<SpanProcessor>>,
     *     },
     *     meter_provider: array{
     *         views: list<array{
     *             stream: array{
     *                 name: ?string,
     *                 description: ?string,
     *                 attribute_keys: list<string>,
     *                 aggregation: ?ComponentPlugin<AggregationResolver>,
     *             },
     *             selector: array{
     *                 instrument_type: 'counter'|'gauge'|'histogram'|'observable_counter'|'observable_gauge'|'observable_up_down_counter'|'up_down_counter'|null,
     *                 instrument_name: ?string,
     *                 unit: ?string,
     *                 meter_name: ?string,
     *                 meter_version: ?string,
     *                 meter_schema_url: ?string,
     *             },
     *         }>,
     *         readers: list<ComponentPlugin<MetricReader>>,
     *     },
     *     logger_provider: array{
     *         limits: array{
     *             attribute_value_length_limit: ?int<0, max>,
     *             attribute_count_limit: ?int<0, max>,
     *         },
     *         processors: list<ComponentPlugin<LogRecordProcessor>>,
     *     },
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): Configuration
    {
        throw new BadMethodCallException('not implemented');
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('open_telemetry');
        $node
            ->addDefaultsIfNotSet()
            ->ignoreExtraKeys()
            ->children()
                ->scalarNode('file_format')
                    ->isRequired()
                    ->example('0.1')
                    ->validate()->always(Validation::ensureString())->end()
                    ->validate()->ifNotInArray(['0.1'])->thenInvalid('unsupported version')->end()
                ->end()
                ->booleanNode('disabled')->defaultFalse()->end()
                ->append($this->getResourceConfig($builder))
                ->append($this->getAttributeLimitsConfig($builder))
                ->append($registry->component('propagator', TextMapPropagatorInterface::class))
                ->append($this->getTracerProviderConfig($registry, $builder))
                ->append($this->getMeterProviderConfig($registry, $builder))
                ->append($this->getLoggerProviderConfig($registry, $builder))
                ->append($registry->component('response_propagator/development', ResponsePropagatorInterface::class))
            ->end();

        return $node;
    }

    private function getResourceConfig(NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('resource');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('attributes')
                    ->variablePrototype()->end()
                ->end()
                ->scalarNode('schema_url')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
            ->end();

        return $node;
    }

    private function getAttributeLimitsConfig(NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('attribute_limits');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->integerNode('attribute_value_length_limit')->min(0)->defaultNull()->end()
                ->integerNode('attribute_count_limit')->min(0)->defaultValue(128)->end()
            ->end();

        return $node;
    }

    private function getTracerProviderConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('tracer_provider');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('limits')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('attribute_value_length_limit')->min(0)->defaultNull()->end()
                        ->integerNode('attribute_count_limit')->min(0)->defaultNull()->end()
                        ->integerNode('event_count_limit')->min(0)->defaultValue(128)->end()
                        ->integerNode('link_count_limit')->min(0)->defaultValue(128)->end()
                        ->integerNode('event_attribute_count_limit')->min(0)->defaultNull()->end()
                        ->integerNode('link_attribute_count_limit')->min(0)->defaultNull()->end()
                    ->end()
                ->end()
                ->append($registry->component('sampler', Sampler::class))
                ->append($registry->componentList('processors', SpanProcessor::class))
            ->end()
        ;

        return $node;
    }

    private function getMeterProviderConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('meter_provider');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('views')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('stream')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('name')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                                    ->scalarNode('description')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                                    ->arrayNode('attribute_keys')
                                        ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                                    ->end()
                                    ->append($registry->component('aggregation', AggregationResolver::class))
                                ->end()
                            ->end()
                            ->arrayNode('selector')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->enumNode('instrument_type')
                                        ->values([
                                            'counter',
                                            'gauge',
                                            'histogram',
                                            'observable_counter',
                                            'observable_gauge',
                                            'observable_up_down_counter',
                                            'up_down_counter',
                                        ])
                                        ->defaultNull()
                                    ->end()
                                    ->scalarNode('instrument_name')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                                    ->scalarNode('unit')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                                    ->scalarNode('meter_name')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                                    ->scalarNode('meter_version')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                                    ->scalarNode('meter_schema_url')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->append($registry->componentList('readers', MetricReader::class))
            ->end()
        ;

        return $node;
    }

    private function getLoggerProviderConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('logger_provider');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('limits')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('attribute_value_length_limit')->min(0)->defaultNull()->end()
                        ->integerNode('attribute_count_limit')->min(0)->defaultNull()->end()
                    ->end()
                ->end()
                ->append($registry->componentList('processors', LogRecordProcessor::class))
            ->end()
        ;

        return $node;
    }
}
