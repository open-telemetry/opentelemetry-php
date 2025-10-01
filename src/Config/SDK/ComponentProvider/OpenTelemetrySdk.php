<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\Config\SDK\Parser\AttributesParser;
use OpenTelemetry\Context\Propagation\MultiResponsePropagator;
use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use OpenTelemetry\Context\Propagation\NoopResponsePropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Logs\EventLoggerProvider;
use OpenTelemetry\SDK\Logs\LoggerConfig;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\MeterConfig;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandler\NoopStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\AllCriteria;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\InstrumentationScopeNameCriteria;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\InstrumentationScopeSchemaUrlCriteria;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\InstrumentationScopeVersionCriteria;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\InstrumentNameCriteria;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\InstrumentTypeCriteria;
use OpenTelemetry\SDK\Metrics\View\ViewTemplate;
use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\SdkBuilder;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanLimits;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerConfig;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @internal
 *
 * @implements ComponentProvider<SdkBuilder>
 */
final class OpenTelemetrySdk implements ComponentProvider
{
    /**
     * @param array{
     *     file_format: '0.4',
     *     disabled: bool,
     *     resource: array{
     *         attributes: array{
     *             array{
     *                 name: string,
     *                 value: mixed,
     *                 type: ?string,
     *             },
     *         },
     *         attributes_list: ?string,
     *         detectors: array,
     *         schema_url: ?string,
     *         "detection/development": ?array{
     *             attributes: array{
     *                 included: list<string>,
     *                 excluded: list<string>,
     *             },
     *             detectors: list<ComponentPlugin<ResourceDetectorInterface>>,
     *         }
     *     },
     *     attribute_limits: array{
     *         attribute_value_length_limit: ?int<0, max>,
     *         attribute_count_limit: int<0, max>,
     *     },
     *     propagator: array{
     *         composite: list<ComponentPlugin<TextMapPropagatorInterface>>,
     *     },
     *     "response_propagator/development": array{
     *         composite: list<ComponentPlugin<ResponsePropagatorInterface>>,
     *     },
     *     tracer_provider: array{
     *         limits: array{
     *             attribute_value_length_limit: ?int<0, max>,
     *             attribute_count_limit: ?int<0, max>,
     *             event_count_limit: int<0, max>,
     *             link_count_limit: int<0, max>,
     *             event_attribute_count_limit: ?int<0, max>,
     *             link_attribute_count_limit: ?int<0, max>,
     *         },
     *         sampler: ?ComponentPlugin<SamplerInterface>,
     *         processors: list<ComponentPlugin<SpanProcessorInterface>>,
     *         "tracer_configurator/development": ?array{
     *              default_config: array{
     *                  disabled: bool,
     *              },
     *              tracers: list<array{
     *                  name: string,
     *                  config: array{
     *                      disabled: bool,
     *                  }
     *              }>
     *           }
     *     },
     *     meter_provider: array{
     *         views: list<array{
     *             stream: array{
     *                 name: ?string,
     *                 description: ?string,
     *                 aggregation_cardinality_limit: ?int<0, max>,
     *                 attribute_keys: array{
     *                     included: list<string>,
     *                     excluded: list<string>,
     *                 },
     *                 aggregation: ?ComponentPlugin<DefaultAggregationProviderInterface>,
     *             },
     *             selector: array{
     *                 instrument_type: 'counter'|'histogram'|'observable_counter'|'observable_gauge'|'observable_up_down_counter'|'up_down_counter'|null,
     *                 instrument_name: ?non-empty-string,
     *                 unit: ?string,
     *                 meter_name: ?string,
     *                 meter_version: ?string,
     *                 meter_schema_url: ?string,
     *             },
     *         }>,
     *         readers: list<ComponentPlugin<MetricReaderInterface>>,
     *         exemplar_filter: 'trace_based'|'always_on'|'always_off',
     *         "meter_configurator/development": ?array{
     *             default_config: array{
     *                 disabled: bool,
     *             },
     *             meters: list<array{
     *                 name: string,
     *                 config: array{
     *                     disabled: bool,
     *                 }
     *             }>
     *          },
     *     },
     *     logger_provider: array{
     *         limits: array{
     *             attribute_value_length_limit: ?int<0, max>,
     *             attribute_count_limit: ?int<0, max>,
     *         },
     *         processors: list<ComponentPlugin<LogRecordProcessorInterface>>,
     *         "logger_configurator/development": ?array{
     *            default_config: array{
     *                disabled: bool,
     *            },
     *            loggers: list<array{
     *                name: string,
     *                config: array{
     *                    disabled: bool,
     *                }
     *            }>
     *         },
     *     },
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): SdkBuilder
    {
        $sdkBuilder = new SdkBuilder();

        $propagators = [];
        foreach ($properties['propagator']['composite'] as $plugin) {
            $propagators[] = $plugin->create($context);
        }
        $propagator = ($propagators === []) ? NoopTextMapPropagator::getInstance() : new MultiTextMapPropagator($propagators);
        $sdkBuilder->setPropagator($propagator);

        $responsePropagators = [];
        foreach ($properties['response_propagator/development']['composite'] as $plugin) {
            $responsePropagators[] = $plugin->create($context);
        }
        $responsePropagator = ($responsePropagators === []) ? NoopResponsePropagator::getInstance() : new MultiResponsePropagator($responsePropagators);
        $sdkBuilder->setResponsePropagator($responsePropagator);

        if ($properties['disabled']) {
            return $sdkBuilder;
        }

        //priorities: 1. attributes 2. attributes_list, 3. detected (after applying include/exclude)
        $schemaUrl = $properties['resource']['schema_url'];
        /** @var ResourceDetectorInterface[] $detectors */
        $detectors = [];
        foreach ($properties['resource']['detection/development']['detectors'] ?? [] as $plugin) {
            /**
             * @psalm-suppress InvalidMethodCall
             **/
            $detectors[] = $plugin->create($context);
        }
        $mandatory = (new Detectors\Composite([
            new Detectors\Sdk(),
            new Detectors\Service(),
        ]))->getResource();

        /** @psalm-suppress PossiblyInvalidArgument */
        $composite = new Detectors\Composite($detectors);
        $included = $properties['resource']['detection/development']['attributes']['included'] ?? null;
        $excluded = $properties['resource']['detection/development']['attributes']['excluded'] ?? [];

        $resource = $composite->getResource();
        $attrs = AttributesParser::applyIncludeExclude($resource->getAttributes()->toArray(), $included, $excluded);
        $resource = ResourceInfo::create(Attributes::create($attrs), $resource->getSchemaUrl());

        $attributes = AttributesParser::parseAttributesList($properties['resource']['attributes_list']);
        $attributes = array_merge($attributes, AttributesParser::parseAttributes($properties['resource']['attributes']));

        $resource = $resource
            ->merge(ResourceInfo::create(
                attributes: Attributes::create($attributes),
                schemaUrl: $schemaUrl,
            ))
            ->merge($mandatory);

        $spanProcessors = [];
        foreach ($properties['tracer_provider']['processors'] as $processor) {
            $spanProcessors[] = $processor->create($context);
        }

        $disabled = $properties['tracer_provider']['tracer_configurator/development']['default_config']['disabled'] ?? false;
        $configurator = Configurator::tracer()->with(static fn (TracerConfig $config) => $config->setDisabled($disabled), null);

        foreach ($properties['tracer_provider']['tracer_configurator/development']['tracers'] ?? [] as $tracer) {
            $disabled = $tracer['config']['disabled'];
            $configurator = $configurator->with(
                static fn (TracerConfig $config) => $config->setDisabled($disabled),
                name: $tracer['name'],
            );
        }

        // <editor-fold desc="tracer_provider">

        $tracerProvider = new TracerProvider(
            spanProcessors: $spanProcessors,
            sampler: ($properties['tracer_provider']['sampler'] ?? null)?->create($context) ?? new ParentBased(new AlwaysOnSampler()),
            resource: $resource,
            spanLimits: new SpanLimits(
                attributesFactory: Attributes::factory(
                    attributeCountLimit: $properties['tracer_provider']['limits']['attribute_count_limit']
                        ?? $properties['attribute_limits']['attribute_count_limit'],
                    attributeValueLengthLimit: $properties['tracer_provider']['limits']['attribute_value_length_limit']
                        ?? $properties['attribute_limits']['attribute_value_length_limit'],
                ),
                eventAttributesFactory: Attributes::factory(
                    attributeCountLimit: $properties['tracer_provider']['limits']['event_attribute_count_limit']
                        ?? $properties['tracer_provider']['limits']['attribute_count_limit']
                        ?? $properties['attribute_limits']['attribute_count_limit'],
                    attributeValueLengthLimit: $properties['tracer_provider']['limits']['attribute_value_length_limit']
                        ?? $properties['attribute_limits']['attribute_value_length_limit'],
                ),
                linkAttributesFactory: Attributes::factory(
                    attributeCountLimit: $properties['tracer_provider']['limits']['link_attribute_count_limit']
                        ?? $properties['tracer_provider']['limits']['attribute_count_limit']
                        ?? $properties['attribute_limits']['attribute_count_limit'],
                    attributeValueLengthLimit: $properties['tracer_provider']['limits']['attribute_value_length_limit']
                        ?? $properties['attribute_limits']['attribute_value_length_limit'],
                ),
                eventCountLimit: $properties['tracer_provider']['limits']['event_count_limit'],
                linkCountLimit: $properties['tracer_provider']['limits']['link_count_limit'],
            ),
            configurator: $configurator,
        );

        // </editor-fold>

        // <editor-fold desc="meter_provider">

        $metricReaders = [];
        foreach ($properties['meter_provider']['readers'] as $reader) {
            $metricReaders[] = $reader->create($context);
        }

        $viewRegistry = new CriteriaViewRegistry();
        foreach ($properties['meter_provider']['views'] as $view) {
            $criteria = [];
            if (isset($view['selector']['instrument_type'])) {
                $criteria[] = new InstrumentTypeCriteria(match ($view['selector']['instrument_type']) {
                    'counter' => InstrumentType::COUNTER,
                    'histogram' => InstrumentType::HISTOGRAM,
                    'observable_counter' => InstrumentType::ASYNCHRONOUS_COUNTER,
                    'observable_gauge' => InstrumentType::ASYNCHRONOUS_GAUGE,
                    'observable_up_down_counter' => InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER,
                    'up_down_counter' => InstrumentType::UP_DOWN_COUNTER,
                });
            }
            if (isset($view['selector']['instrument_name'])) {
                $criteria[] = new InstrumentNameCriteria($view['selector']['instrument_name']);
            }
            if (isset($view['selector']['unit'])) {
                // TODO Add unit criteria
            }
            if (isset($view['selector']['meter_name'])) {
                $criteria[] = new InstrumentationScopeNameCriteria($view['selector']['meter_name']);
            }
            if (isset($view['selector']['meter_version'])) {
                $criteria[] = new InstrumentationScopeVersionCriteria($view['selector']['meter_version']);
            }
            if (isset($view['selector']['meter_schema_url'])) {
                $criteria[] = new InstrumentationScopeSchemaUrlCriteria($view['selector']['meter_schema_url']);
            }

            $viewTemplate = ViewTemplate::create();
            if (isset($view['stream']['name'])) {
                $viewTemplate = $viewTemplate->withName($view['stream']['name']);
            }
            if (isset($view['stream']['description'])) {
                $viewTemplate = $viewTemplate->withDescription($view['stream']['description']);
            }
            // TODO Add support for excluded keys to view template
            if ($view['stream']['attribute_keys']['included']) {
                $viewTemplate = $viewTemplate->withAttributeKeys($view['stream']['attribute_keys']['included']);
            }
            if (isset($view['stream']['aggregation'])) {
                // TODO Add support for aggregation providers in views to allow usage of advisory
            }

            $viewRegistry->register(new AllCriteria($criteria), $viewTemplate);
        }

        $disabled = $properties['meter_provider']['meter_configurator/development']['default_config']['disabled'] ?? false;
        $configurator = Configurator::meter()->with(static fn (MeterConfig $config) => $config->setDisabled($disabled), null);
        foreach ($properties['meter_provider']['meter_configurator/development']['meters'] ?? [] as $meter) {
            $disabled = $meter['config']['disabled'];
            $configurator = $configurator->with(
                static fn (MeterConfig $config) => $config->setDisabled($disabled),
                name: $meter['name'],
            );
        }

        /** @psalm-suppress InvalidArgument TODO update metric reader interface */
        $meterProvider = new MeterProvider(
            contextStorage: null,
            resource: $resource,
            clock: Clock::getDefault(),
            attributesFactory: Attributes::factory(),
            instrumentationScopeFactory: new InstrumentationScopeFactory(Attributes::factory()),
            metricReaders: $metricReaders, // @phpstan-ignore-line
            viewRegistry: $viewRegistry,
            exemplarFilter: null,
            stalenessHandlerFactory: new NoopStalenessHandlerFactory(),
            configurator: $configurator,
        );

        // </editor-fold>

        // <editor-fold desc="logger_provider">

        $logRecordProcessors = [];
        foreach ($properties['logger_provider']['processors'] as $processor) {
            $logRecordProcessors[] = $processor->create($context);
        }

        $disabled = $properties['logger_provider']['logger_configurator/development']['default_config']['disabled'] ?? false;
        $configurator = Configurator::logger()->with(static fn (LoggerConfig $config) => $config->setDisabled($disabled), null);
        foreach ($properties['logger_provider']['logger_configurator/development']['loggers'] ?? [] as $logger) {
            $disabled = $logger['config']['disabled'];
            $configurator = $configurator->with(
                static fn (LoggerConfig $config) => $config->setDisabled($disabled),
                name: $logger['name'],
            );
        }

        // TODO Allow injecting log record attributes factory
        $loggerProvider = new LoggerProvider(
            processor: new MultiLogRecordProcessor($logRecordProcessors),
            instrumentationScopeFactory: new InstrumentationScopeFactory(Attributes::factory()),
            resource: $resource,
            configurator: $configurator,
        );
        $eventLoggerProvider = new EventLoggerProvider($loggerProvider);

        // </editor-fold>

        $sdkBuilder->setTracerProvider($tracerProvider);
        $sdkBuilder->setMeterProvider($meterProvider);
        $sdkBuilder->setLoggerProvider($loggerProvider);
        $sdkBuilder->setEventLoggerProvider($eventLoggerProvider);

        return $sdkBuilder;
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
                    ->validate()->ifNotInArray(['0.4'])->thenInvalid('unsupported version')->end()
                ->end()
                ->booleanNode('disabled')->defaultFalse()->end()
                ->append($this->getResourceConfig($registry, $builder))
                ->append($this->getAttributeLimitsConfig($builder))
                ->append($this->getPropagatorConfig($registry, $builder))
                ->append($this->getTracerProviderConfig($registry, $builder))
                ->append($this->getMeterProviderConfig($registry, $builder))
                ->append($this->getLoggerProviderConfig($registry, $builder))
                ->append($this->getExperimentalResponsePropagatorConfig($registry, $builder))
            ->end();

        return $node;
    }

    private function getResourceConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('resource');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('attributes')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->variableNode('value')->isRequired()->end()
                            ->enumNode('type')->defaultNull()
                                ->values(['string', 'bool', 'int', 'double', 'string_array', 'bool_array', 'int_array', 'double_array'])
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('attributes_list')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                ->arrayNode('detection/development')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('attributes')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('included')
                                    ->defaultNull()
                                    ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                                ->end()
                                ->arrayNode('excluded')
                                    ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->append($registry->componentList('detectors', ResourceDetectorInterface::class))
                    ->end()
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
                ->append($registry->component('sampler', SamplerInterface::class))
                ->append($registry->componentList('processors', SpanProcessorInterface::class))
                ->arrayNode('tracer_configurator/development')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('default_config')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('disabled')->isRequired()->end()
                            ->end()
                        ->end()
                        ->arrayNode('tracers')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('config')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->booleanNode('disabled')->isRequired()->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
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
                ->enumNode('exemplar_filter')
                    ->values([
                        'trace_based',
                        'always_on',
                        'always_off',
                    ])
                    ->defaultValue('trace_based')
                ->end()
                ->arrayNode('views')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('stream')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('name')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                                    ->scalarNode('description')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                                    ->integerNode('aggregation_cardinality_limit')->defaultValue(2000)->end()
                                    ->arrayNode('attribute_keys')
                                        ->children()
                                            ->arrayNode('included')
                                                ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                                            ->end()
                                            ->arrayNode('excluded')
                                                ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->append($registry->component('aggregation', DefaultAggregationProviderInterface::class))
                                ->end()
                            ->end()
                            ->arrayNode('selector')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->enumNode('instrument_type')
                                        ->values([
                                            'counter',
                                            'histogram',
                                            'observable_counter',
                                            'observable_gauge',
                                            'observable_up_down_counter',
                                            'up_down_counter',
                                        ])
                                        ->defaultNull()
                                    ->end()
                                    ->scalarNode('instrument_name')->defaultNull()->validate()->always(Validation::ensureString())->end()->cannotBeEmpty()->end()
                                    ->scalarNode('unit')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                                    ->scalarNode('meter_name')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                                    ->scalarNode('meter_version')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                                    ->scalarNode('meter_schema_url')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->append($registry->componentList('readers', MetricReaderInterface::class))
                ->arrayNode('meter_configurator/development')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('default_config')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('disabled')->isRequired()->end()
                            ->end()
                        ->end()
                        ->arrayNode('meters')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('config')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->booleanNode('disabled')->isRequired()->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
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
                ->append($registry->componentList('processors', LogRecordProcessorInterface::class))
                ->arrayNode('logger_configurator/development')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('default_config')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('disabled')->isRequired()->end()
                            ->end()
                        ->end()
                        ->arrayNode('loggers')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('config')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->booleanNode('disabled')->isRequired()->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getPropagatorConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('propagator');
        $node
            ->beforeNormalization()
                ->ifArray()
                ->then(static function (array $value): array {
                    $existing = [];
                    foreach ($value['composite'] ?? [] as $item) {
                        $existing[] = key($item);
                    }
                    foreach (explode(',', $value['composite_list'] ?? '') as $name) {
                        $name = trim($name);
                        if ($name !== '' && !in_array($name, $existing)) {
                            $value['composite'][][$name] = null;
                            $existing[] = $name;
                        }
                    }

                    unset($value['composite_list']);

                    return $value;
                })
            ->end();

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->append($registry->componentList('composite', TextMapPropagatorInterface::class))
//                ->arrayNode('composite_list')
//                    ->scalarPrototype()->end()
//                ->end()
            ->end()
        ;

        return $node;
    }

    private function getExperimentalResponsePropagatorConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('response_propagator/development');
        $node
            ->beforeNormalization()
            ->ifArray()
            ->then(static function (array $value): array {
                $existing = [];
                foreach ($value['composite'] ?? [] as $item) {
                    $existing[] = key($item);
                }
                foreach (explode(',', $value['composite_list'] ?? '') as $name) {
                    $name = trim($name);
                    if ($name !== '' && !in_array($name, $existing)) {
                        $value['composite'][][$name] = null;
                        $existing[] = $name;
                    }
                }

                unset($value['composite_list']);

                return $value;
            })
            ->end();

        $node
            ->addDefaultsIfNotSet()
            ->children()
            ->append($registry->componentList('composite', ResponsePropagatorInterface::class))
            ->end()
        ;

        return $node;
    }
}
