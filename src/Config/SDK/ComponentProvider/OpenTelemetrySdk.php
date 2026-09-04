<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\SemVer;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\Config\SDK\Parser\AttributesParser;
use OpenTelemetry\Context\Propagation\MultiResponsePropagator;
use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use OpenTelemetry\Context\Propagation\NoopResponsePropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Distribution\DistributionConfiguration;
use OpenTelemetry\SDK\Common\Distribution\DistributionRegistry;
use OpenTelemetry\SDK\Common\Distribution\SdkDistribution;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Logs\EventLoggerProvider;
use OpenTelemetry\SDK\Logs\LoggerConfig;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
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
use OpenTelemetry\SDK\Trace\IdGeneratorInterface;
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
     *     file_format: '1.0-rc.2'|'1.1',
     *     disabled: bool,
     *     log_level: ?string,
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
     *         attribute_value_depth_limit: ?int<1, max>,
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
     *             attribute_value_depth_limit: ?int<1, max>,
     *             attribute_count_limit: ?int<0, max>,
     *             event_count_limit: int<0, max>,
     *             link_count_limit: int<0, max>,
     *             event_attribute_count_limit: ?int<0, max>,
     *             link_attribute_count_limit: ?int<0, max>,
     *         },
     *         sampler: ?ComponentPlugin<SamplerInterface>,
     *         id_generator: ?ComponentPlugin<IdGeneratorInterface>,
     *         processors: list<ComponentPlugin<SpanProcessorInterface>>,
     *         "tracer_configurator/development": ?array{
     *              default_config: array{
     *                  disabled: bool,
     *              },
     *              tracers: list<array{
     *                  name: string,
     *                  config: array{
     *                      disabled: ?bool,
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
     *             attribute_value_depth_limit: ?int<1, max>,
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
     *     distribution: list<ComponentPlugin<DistributionConfiguration>>,
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

        $distributionProperties = new DistributionRegistry();
        foreach ($properties['distribution'] as $distributionConfiguration) {
            $distributionProperties->add($distributionConfiguration->create($context));
        }

        $distributionConfiguration = $distributionProperties->getDistributionConfiguration(SdkDistribution::class) ?? new SdkDistribution();

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

        $resource = $mandatory
            ->merge($resource)
            ->merge(ResourceInfo::create(
                attributes: Attributes::create($attributes),
                schemaUrl: $schemaUrl,
            ));

        $context = $context->withExtension($resource, ResourceInfo::class);

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
            exemplarFilter: match ($properties['meter_provider']['exemplar_filter']) {
                'trace_based' => new WithSampledTraceExemplarFilter(),
                'always_on'   => new AllExemplarFilter(),
                'always_off'  => new NoneExemplarFilter(),
            },
            stalenessHandlerFactory: new NoopStalenessHandlerFactory(),
            configurator: $configurator,
        );

        // </editor-fold>

        $context = $context->withMeterProvider($meterProvider);

        $spanProcessors = [];
        foreach ($properties['tracer_provider']['processors'] as $processor) {
            $spanProcessors[] = $processor->create($context);
        }

        $disabled = $properties['tracer_provider']['tracer_configurator/development']['default_config']['disabled'] ?? false;
        $configurator = Configurator::tracer()->with(static fn (TracerConfig $config) => $config->setDisabled($disabled), null);

        foreach ($properties['tracer_provider']['tracer_configurator/development']['tracers'] ?? [] as $tracer) {
            $disabled = $tracer['config']['disabled'] ?? false;
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
            idGenerator: ($properties['tracer_provider']['id_generator'] ?? null)?->create($context),
            configurator: $configurator,
            spanSuppressionStrategy: $distributionConfiguration->spanSuppressionStrategy,
            meterProvider: $meterProvider,
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

        $logRecordLimitsBuilder = new LogRecordLimitsBuilder();
        if ($properties['logger_provider']['limits']['attribute_count_limit'] !== null) {
            $logRecordLimitsBuilder->setAttributeCountLimit($properties['logger_provider']['limits']['attribute_count_limit']);
        }
        if ($properties['logger_provider']['limits']['attribute_value_length_limit'] !== null) {
            $logRecordLimitsBuilder->setAttributeValueLengthLimit($properties['logger_provider']['limits']['attribute_value_length_limit']);
        }

        $loggerProvider = new LoggerProvider(
            processor: new MultiLogRecordProcessor($logRecordProcessors),
            instrumentationScopeFactory: new InstrumentationScopeFactory(Attributes::factory()),
            resource: $resource,
            configurator: $configurator,
            meterProvider: $meterProvider,
            logRecordLimits: $logRecordLimitsBuilder->build(),
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
            ->beforeNormalization()
                ->ifArray()
                ->then(static function (array $v): array {
                    if (!is_string($v['file_format'] ?? null)) {
                        return $v;
                    }

                    self::validateV11OnlyFields($v, $v['file_format']);

                    return self::normalizeConfigurators($v, $v['file_format']);
                })
            ->end()
            ->children()
                ->scalarNode('file_format')
                    ->isRequired()
                    ->example('0.1')
                    ->validate()->always(Validation::ensureString())->end()
                    ->validate()->ifNotInArray(['1.0-rc.2', '1.1'])->thenInvalid('unsupported version')->end()
                ->end()
                ->booleanNode('disabled')->defaultFalse()->end()
                ->enumNode('log_level')
                    ->values([
                        'trace', 'trace2', 'trace3', 'trace4',
                        'debug', 'debug2', 'debug3', 'debug4',
                        'info', 'info2', 'info3', 'info4',
                        'warn', 'warn2', 'warn3', 'warn4',
                        'error', 'error2', 'error3', 'error4',
                        'fatal', 'fatal2', 'fatal3', 'fatal4',
                    ])
                    ->defaultNull()
                    // TODO: apply to SDK internal logger once a control surface exists
                ->end()
                ->append($this->getResourceConfig($registry, $builder))
                ->append($this->getAttributeLimitsConfig($builder))
                ->append($this->getPropagatorConfig($registry, $builder))
                ->append($this->getTracerProviderConfig($registry, $builder))
                ->append($this->getMeterProviderConfig($registry, $builder))
                ->append($this->getLoggerProviderConfig($registry, $builder))
                ->append($this->getExperimentalResponsePropagatorConfig($registry, $builder))
                ->append($registry->componentMap('distribution', DistributionConfiguration::class)->defaultValue([]))
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
                ->integerNode('attribute_value_depth_limit')->min(1)->defaultNull()->end() // TODO: enforce once Attributes::factory() supports depth limits
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
                        ->integerNode('attribute_value_depth_limit')->min(1)->defaultNull()->end() // TODO: enforce once Attributes::factory() supports depth limits
                        ->integerNode('attribute_count_limit')->min(0)->defaultNull()->end()
                        ->integerNode('event_count_limit')->min(0)->defaultValue(128)->end()
                        ->integerNode('link_count_limit')->min(0)->defaultValue(128)->end()
                        ->integerNode('event_attribute_count_limit')->min(0)->defaultNull()->end()
                        ->integerNode('link_attribute_count_limit')->min(0)->defaultNull()->end()
                    ->end()
                ->end()
                ->append($registry->component('sampler', SamplerInterface::class))
                ->append($registry->component('id_generator', IdGeneratorInterface::class))
                ->append($registry->componentList('processors', SpanProcessorInterface::class))
                ->arrayNode('tracer_configurator/development')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('default_config')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('disabled')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->arrayNode('tracers')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->arrayNode('config')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->booleanNode('disabled')->defaultFalse()->end()
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
                                ->booleanNode('disabled')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->arrayNode('meters')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('config')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->booleanNode('disabled')->defaultFalse()->end()
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
                        ->integerNode('attribute_value_depth_limit')->min(1)->defaultNull()->end() // TODO: enforce once Attributes::factory() supports depth limits
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
                                ->booleanNode('disabled')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->arrayNode('loggers')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('config')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->booleanNode('disabled')->defaultFalse()->end()
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

    /**
     * Reject fields that were introduced in file_format "1.1" when used in an older version.
     *
     * This runs in the root beforeNormalization hook where file_format is in scope.
     * Nested component providers (OTLP exporters etc.) cannot access file_format, so
     * any 1.1-only fields that live in their schemas are accepted silently for pre-1.1
     * configs when they have no behavioural effect (e.g. max_request_size, which is
     * currently a no-op / TODO).
     *
     * @param array<string, mixed> $v raw user-supplied root array (before Symfony defaults)
     * @throws \InvalidArgumentException when a 1.1-only field is used in a pre-1.1 config
     */
    private static function validateV11OnlyFields(array $v, string $version): void
    {
        if (SemVer::gte($version, '1.1')) {
            return;
        }

        // attribute_limits.attribute_value_depth_limit
        if (array_key_exists('attribute_value_depth_limit', $v['attribute_limits'] ?? [])) {
            throw new \InvalidArgumentException(sprintf(
                '"attribute_limits.attribute_value_depth_limit" is not supported in file_format "%s"; it was introduced in "1.1".',
                $version,
            ));
        }

        // tracer_provider.limits.attribute_value_depth_limit
        if (array_key_exists('attribute_value_depth_limit', $v['tracer_provider']['limits'] ?? [])) {
            throw new \InvalidArgumentException(sprintf(
                '"tracer_provider.limits.attribute_value_depth_limit" is not supported in file_format "%s"; it was introduced in "1.1".',
                $version,
            ));
        }

        // tracer_provider.id_generator
        if (array_key_exists('id_generator', $v['tracer_provider'] ?? [])) {
            throw new \InvalidArgumentException(sprintf(
                '"tracer_provider.id_generator" is not supported in file_format "%s"; it was introduced in "1.1".',
                $version,
            ));
        }

        // logger_provider.limits.attribute_value_depth_limit
        if (array_key_exists('attribute_value_depth_limit', $v['logger_provider']['limits'] ?? [])) {
            throw new \InvalidArgumentException(sprintf(
                '"logger_provider.limits.attribute_value_depth_limit" is not supported in file_format "%s"; it was introduced in "1.1".',
                $version,
            ));
        }
    }

    /**
     * Apply version-specific normalization to all configurator nodes at the root level,
     * where file_format is in scope. Nested beforeNormalization hooks cannot access
     * file_format, making the root the only correct place for version-aware logic.
     *
     * @param array<string, mixed> $v
     * @return array<string, mixed>
     */
    private static function normalizeConfigurators(array $v, string $version): array
    {
        foreach ([
            ['tracer_provider', 'tracer_configurator/development', 'tracers'],
            ['meter_provider', 'meter_configurator/development', 'meters'],
            ['logger_provider', 'logger_configurator/development', 'loggers'],
        ] as [$provider, $configurator, $scopeKey]) {
            if (!array_key_exists($provider, $v) || !array_key_exists($configurator, $v[$provider])) {
                continue;
            }

            $basePath = $provider . '.' . $configurator;
            $cfg = &$v[$provider][$configurator];

            $cfg = self::normalizeEnabledField($cfg, $version, $basePath);

            if (is_array($cfg['default_config'] ?? null)) {
                $cfg['default_config'] = self::normalizeEnabledField(
                    $cfg['default_config'],
                    $version,
                    $basePath . '.default_config',
                );
            }

            foreach ($cfg[$scopeKey] ?? [] as $i => $scope) {
                if (!is_array($scope)) {
                    continue;
                }
                $cfg[$scopeKey][$i] = self::normalizeEnabledField(
                    $scope,
                    $version,
                    sprintf('%s.%s[%d]', $basePath, $scopeKey, $i),
                );
                if (is_array($scope['config'] ?? null)) {
                    $cfg[$scopeKey][$i]['config'] = self::normalizeEnabledField(
                        $scope['config'],
                        $version,
                        sprintf('%s.%s[%d].config', $basePath, $scopeKey, $i),
                    );
                }
            }
        }

        return $v;
    }

    /**
     * Normalize the enabled/disabled field for a single configurator node, with version awareness.
     *
     * - file_format >= 1.1: converts `enabled: bool` → `disabled: bool` (the canonical internal field).
     *   Using the old `disabled` field triggers a deprecation notice with an actionable hint.
     * - file_format < 1.1: `enabled` is not a valid field; throws with a descriptive error.
     *
     * @param array<string, mixed> $value
     * @return array<string, mixed>
     * @throws \InvalidArgumentException when `enabled` is used in a pre-1.1 file_format
     */
    private static function normalizeEnabledField(array $value, string $version, string $path): array
    {
        // Check for the deprecated `disabled` field before we potentially add it ourselves via
        // the enabled→disabled conversion below. Only warn when the user explicitly provided
        // `disabled` without the new `enabled` field.
        if (array_key_exists('disabled', $value) && !array_key_exists('enabled', $value) && SemVer::gte($version, '1.1')) {
            trigger_error(sprintf(
                '"%s.disabled" is deprecated since file_format "1.1"; use "enabled: %s" instead.',
                $path,
                $value['disabled'] ? 'false' : 'true',
            ), \E_USER_DEPRECATED);
        }

        if (array_key_exists('enabled', $value)) {
            if (SemVer::lt($version, '1.1')) {
                throw new \InvalidArgumentException(sprintf(
                    '"%s.enabled" is not supported in file_format "%s"; use "disabled: %s" instead.',
                    $path,
                    $version,
                    $value['enabled'] ? 'false' : 'true',
                ));
            }

            // 1.1+: convert enabled → disabled (the canonical internal field)
            if (!array_key_exists('disabled', $value)) {
                $value['disabled'] = !$value['enabled'];
            }
            unset($value['enabled']);
        }

        return $value;
    }

    private function getPropagatorConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('propagator');
        $node
            ->beforeNormalization()
                ->ifArray()
                ->then(static function (array $value): array {
                    // Normalize composite entries: bare string "name" → ["name" => null]
                    $normalized = [];
                    foreach ($value['composite'] ?? [] as $item) {
                        if (is_string($item)) {
                            $normalized[] = [$item => null];
                        } else {
                            $normalized[] = $item;
                        }
                    }
                    $value['composite'] = $normalized;

                    $existing = array_map(static fn (array $item) => (string) key($item), $normalized);
                    foreach (explode(',', $value['composite_list'] ?? '') as $name) {
                        $name = trim($name);
                        if ($name !== '' && !in_array($name, $existing, true)) {
                            $value['composite'][] = [$name => null];
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
                // Normalize composite entries: bare string "name" → ["name" => null]
                $normalized = [];
                foreach ($value['composite'] ?? [] as $item) {
                    if (is_string($item)) {
                        $normalized[] = [$item => null];
                    } else {
                        $normalized[] = $item;
                    }
                }
                $value['composite'] = $normalized;

                $existing = array_map(static fn (array $item) => (string) key($item), $normalized);
                foreach (explode(',', $value['composite_list'] ?? '') as $name) {
                    $name = trim($name);
                    if ($name !== '' && !in_array($name, $existing, true)) {
                        $value['composite'][] = [$name => null];
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
