<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\Config\SDK\Configuration\ComponentPlugin;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\InstrumentType;
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
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\SdkBuilder;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanLimits;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @internal
 *
 * @implements ComponentProvider<SdkBuilder>
 */
final class OpenTelemetrySdk implements ComponentProvider
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
     *     },
     *     meter_provider: array{
     *         views: list<array{
     *             stream: array{
     *                 name: ?string,
     *                 description: ?string,
     *                 attribute_keys: list<string>,
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
     *     },
     *     logger_provider: array{
     *         limits: array{
     *             attribute_value_length_limit: ?int<0, max>,
     *             attribute_count_limit: ?int<0, max>,
     *         },
     *         processors: list<ComponentPlugin<LogRecordProcessorInterface>>,
     *     },
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): SdkBuilder
    {
        $sdkBuilder = new SdkBuilder();

        $propagator = $properties['propagator']?->create($context) ?? NoopTextMapPropagator::getInstance();
        $sdkBuilder->setPropagator($propagator);

        if ($properties['disabled']) {
            return $sdkBuilder;
        }

        $resource = ResourceInfoFactory::defaultResource()
            ->merge(ResourceInfo::create(
                attributes: Attributes::create($properties['resource']['attributes']),
                schemaUrl: $properties['resource']['schema_url'],
            ));

        $spanProcessors = [];
        foreach ($properties['tracer_provider']['processors'] as $processor) {
            $spanProcessors[] = $processor->create($context);
        }

        // <editor-fold desc="tracer_provider">

        $tracerProvider = new TracerProvider(
            spanProcessors: $spanProcessors,
            sampler: $properties['tracer_provider']['sampler']?->create($context) ?? new ParentBased(new AlwaysOnSampler()),
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
            if ($view['stream']['attribute_keys']) {
                $viewTemplate = $viewTemplate->withAttributeKeys($view['stream']['attribute_keys']);
            }
            if (isset($view['stream']['aggregation'])) {
                // TODO Add support for aggregation providers in views to allow usage of advisory
            }

            $viewRegistry->register(new AllCriteria($criteria), $viewTemplate);
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
        );

        // </editor-fold>

        // <editor-fold desc="logger_provider">

        $logRecordProcessors = [];
        foreach ($properties['logger_provider']['processors'] as $processor) {
            $logRecordProcessors[] = $processor->create($context);
        }

        // TODO Allow injecting log record attributes factory
        $loggerProvider = new LoggerProvider(
            processor: new MultiLogRecordProcessor($logRecordProcessors),
            instrumentationScopeFactory: new InstrumentationScopeFactory(Attributes::factory()),
            resource: $resource,
        );

        // </editor-fold>

        $sdkBuilder->setTracerProvider($tracerProvider);
        $sdkBuilder->setMeterProvider($meterProvider);
        $sdkBuilder->setLoggerProvider($loggerProvider);

        return $sdkBuilder;
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('open_telemetry');
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
                ->append($this->getResourceConfig())
                ->append($this->getAttributeLimitsConfig())
                ->append($registry->component('propagator', TextMapPropagatorInterface::class))
                ->append($this->getTracerProviderConfig($registry))
                ->append($this->getMeterProviderConfig($registry))
                ->append($this->getLoggerProviderConfig($registry))
            ->end();

        return $node;
    }

    private function getResourceConfig(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('resource');
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

    private function getAttributeLimitsConfig(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('attribute_limits');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->integerNode('attribute_value_length_limit')->min(0)->defaultNull()->end()
                ->integerNode('attribute_count_limit')->min(0)->defaultValue(128)->end()
            ->end();

        return $node;
    }

    private function getTracerProviderConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('tracer_provider');
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
            ->end()
        ;

        return $node;
    }

    private function getMeterProviderConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('meter_provider');
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
            ->end()
        ;

        return $node;
    }

    private function getLoggerProviderConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('logger_provider');
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
            ->end()
        ;

        return $node;
    }
}
