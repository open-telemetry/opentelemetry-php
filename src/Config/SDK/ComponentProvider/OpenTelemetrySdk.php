<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider;

use OpenTelemetry\API\Behavior\Internal\ConfiguredLogger;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\Config\SDK\Configuration\IgnoresUnknownProviders;
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
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\InstrumentUnitCriteria;
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
use Psr\Log\LogLevel;
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
     * The schema version this SDK targets. `MINOR` versions are additive, so a file declaring a
     * newer one is accepted with a warning; a different `MAJOR` is an error.
     *
     * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/VERSIONING.md#file-format
     */
    private const FILE_FORMAT_MAJOR = 1;
    private const FILE_FORMAT_MINOR = 0;

    /**
     * The schema's documented default for `log_level`.
     *
     * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/schema/opentelemetry_configuration.yaml#L18
     */
    private const DEFAULT_LOG_LEVEL = 'info';

    /**
     * `SeverityNumber` enum values, as defined by the configuration schema. They are the
     * {@see Severity} names lowercased.
     *
     * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/schema/common.yaml#L249
     * @return list<string>
     */
    private static function severityNumbers(): array
    {
        return array_map(strtolower(...), array_column(Severity::cases(), 'name'));
    }

    /**
     * @param array{
     *     file_format: non-empty-string,
     *     disabled: bool,
     *     log_level: ?string,
     *     resource: array{
     *         attributes: list<array{
     *             name: string,
     *             value: mixed,
     *             type: ?string,
     *         }>,
     *         attributes_list: ?string,
     *         schema_url: ?string,
     *         "detection/development": array{
     *             attributes: array{
     *                 included: ?list<string>,
     *                 excluded: list<string>,
     *             },
     *             detectors: list<ComponentPlugin<?ResourceDetectorInterface>>,
     *         }
     *     },
     *     attribute_limits: array{
     *         attribute_value_length_limit: ?int<0, max>,
     *         attribute_count_limit: int<0, max>,
     *     },
     *     propagator: array{
     *         composite_list: ?string,
     *         composite: list<ComponentPlugin<TextMapPropagatorInterface>>,
     *     },
     *     "response_propagator/development"?: array{
     *         composite_list: ?string,
     *         composite: list<ComponentPlugin<ResponsePropagatorInterface>>,
     *     },
     *     tracer_provider?: array{
     *         limits: array{
     *             attribute_value_length_limit: ?int<0, max>,
     *             attribute_count_limit: ?int<0, max>,
     *             event_count_limit: int<0, max>,
     *             link_count_limit: int<0, max>,
     *             event_attribute_count_limit: int<0, max>,
     *             link_attribute_count_limit: int<0, max>,
     *         },
     *         sampler?: ComponentPlugin<SamplerInterface>,
     *         processors: list<ComponentPlugin<SpanProcessorInterface>>,
     *         "tracer_configurator/development": array{
     *              default_config: array{
     *                  enabled: bool,
     *              },
     *              tracers: list<array{
     *                  name: string,
     *                  config: array{
     *                      enabled: bool,
     *                  }
     *              }>
     *           }
     *     },
     *     meter_provider?: array{
     *         views: list<array{
     *             stream: array{
     *                 name: ?string,
     *                 description: ?string,
     *                 aggregation_cardinality_limit: ?int<1, max>,
     *                 attribute_keys?: array{
     *                     included: list<string>,
     *                     excluded: list<string>,
     *                 },
     *                 aggregation?: ComponentPlugin<DefaultAggregationProviderInterface>,
     *             },
     *             selector: array{
     *                 instrument_type: 'counter'|'gauge'|'histogram'|'observable_counter'|'observable_gauge'|'observable_up_down_counter'|'up_down_counter'|null,
     *                 instrument_name: ?non-empty-string,
     *                 unit: ?string,
     *                 meter_name: ?string,
     *                 meter_version: ?string,
     *                 meter_schema_url: ?string,
     *             },
     *         }>,
     *         readers: list<ComponentPlugin<MetricReaderInterface>>,
     *         exemplar_filter: 'trace_based'|'always_on'|'always_off',
     *         "meter_configurator/development": array{
     *             default_config: array{
     *                 enabled: bool,
     *             },
     *             meters: list<array{
     *                 name: string,
     *                 config: array{
     *                     enabled: bool,
     *                 }
     *             }>
     *          },
     *     },
     *     logger_provider?: array{
     *         limits: array{
     *             attribute_value_length_limit: ?int<0, max>,
     *             attribute_count_limit: ?int<0, max>,
     *         },
     *         processors: list<ComponentPlugin<LogRecordProcessorInterface>>,
     *         "logger_configurator/development": array{
     *            default_config: array{
     *                enabled: bool,
     *                minimum_severity: ?string,
     *                trace_based: ?bool,
     *            },
     *            loggers: list<array{
     *                name: string,
     *                config: array{
     *                    enabled: bool,
     *                    minimum_severity: ?string,
     *                    trace_based: ?bool,
     *                }
     *            }>
     *         },
     *     },
     *     distribution: list<ComponentPlugin<?DistributionConfiguration>>,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): SdkBuilder
    {
        $sdkBuilder = new SdkBuilder();

        // Declarative configuration ignores ambient environment unless explicitly substituted, so an
        // omitted `log_level` resolves to the schema default rather than leaving OTEL_LOG_LEVEL to
        // decide.
        $logLevel = Severity::fromName($properties['log_level'] ?? self::DEFAULT_LOG_LEVEL)->toPsr3();
        $sdkBuilder->setLogLevel($logLevel);
        // Applies the configured level to messages emitted while creating components, which all
        // happens before `buildAndRegisterGlobal()` sets the global minimum.
        $context = $context->withLogger(new ConfiguredLogger($logLevel));

        // Warned here rather than during validation, so that it honors `log_level` and is not
        // lost when the processed configuration is read back from the cache.
        if ((int) explode('.', $properties['file_format'])[1] > self::FILE_FORMAT_MINOR) {
            $context->logger->log(LogLevel::WARNING, sprintf(
                'file_format "%s" is newer than the %d.%d implemented by this SDK; configuration it introduces may be ignored',
                $properties['file_format'],
                self::FILE_FORMAT_MAJOR,
                self::FILE_FORMAT_MINOR,
            ));
        }

        $propagators = [];
        foreach ($properties['propagator']['composite'] as $plugin) {
            $propagators[] = $plugin->create($context);
        }
        $propagator = ($propagators === []) ? NoopTextMapPropagator::getInstance() : new MultiTextMapPropagator($propagators);
        $sdkBuilder->setPropagator($propagator);

        $distributionProperties = new DistributionRegistry();
        foreach ($properties['distribution'] as $distributionConfiguration) {
            if (($distribution = $distributionConfiguration->create($context)) !== null) {
                $distributionProperties->add($distribution);
            }
        }

        $distributionConfiguration = $distributionProperties->getDistributionConfiguration(SdkDistribution::class) ?? new SdkDistribution();

        $responsePropagator = $distributionConfiguration->responsePropagator;
        // TODO remove the deprecated top-level response_propagator/development before 1.0
        if (isset($properties['response_propagator/development'])) {
            $context->logger->log(LogLevel::WARNING, '"response_propagator/development" is deprecated, move it under "distribution.opentelemetry_php/development"');

            $responsePropagators = [];
            foreach ($properties['response_propagator/development']['composite'] as $plugin) {
                $responsePropagators[] = $plugin->create($context);
            }
            if ($responsePropagator === null && $responsePropagators !== []) {
                $responsePropagator = new MultiResponsePropagator($responsePropagators);
            }
        }
        $sdkBuilder->setResponsePropagator($responsePropagator ?? NoopResponsePropagator::getInstance());

        if ($properties['disabled']) {
            return $sdkBuilder;
        }

        //priorities: 1. attributes 2. attributes_list, 3. detected (after applying include/exclude)
        $schemaUrl = $properties['resource']['schema_url'];
        /** @var ResourceDetectorInterface[] $detectors */
        $detectors = [];
        foreach ($properties['resource']['detection/development']['detectors'] as $plugin) {
            /**
             * @psalm-suppress InvalidMethodCall
             **/
            if (($detector = $plugin->create($context)) !== null) {
                $detectors[] = $detector;
            }
        }
        $mandatory = (new Detectors\Composite([
            new Detectors\Sdk(),
        ]))->getResource();

        /** @psalm-suppress PossiblyInvalidArgument */
        $composite = new Detectors\Composite($detectors);
        $included = $properties['resource']['detection/development']['attributes']['included'];
        $excluded = $properties['resource']['detection/development']['attributes']['excluded'];

        $resource = $composite->getResource();
        $attrs = AttributesParser::applyIncludeExclude($resource->getAttributes()->toArray(), $included, $excluded);
        $resource = ResourceInfo::create(Attributes::create($attrs), $resource->getSchemaUrl());

        $attributes = AttributesParser::parseAttributesList($properties['resource']['attributes_list']);
        $attributes = array_replace($attributes, AttributesParser::parseAttributes($properties['resource']['attributes']));

        $resource = $mandatory
            ->merge($resource)
            ->merge(ResourceInfo::create(Attributes::create($attributes)));
        if ($schemaUrl !== null) {
            // a merge would drop the configured URL when it differs from the detectors' one
            $resource = ResourceInfo::create($resource->getAttributes(), $schemaUrl);
        }

        $context = $context->withExtension($resource, ResourceInfo::class);

        $meterProvider = null;
        if (isset($properties['meter_provider'])) {
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
                        'gauge' => InstrumentType::GAUGE,
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
                    $criteria[] = new InstrumentUnitCriteria($view['selector']['unit']);
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
                    if (strpbrk($view['selector']['instrument_name'] ?? '*', '*?') !== false) {
                        $context->logger->log(LogLevel::WARNING, sprintf(
                            'View stream name "%s" is used with a selector that can match more than one instrument',
                            $view['stream']['name'],
                        ));
                    }
                }
                if (isset($view['stream']['description'])) {
                    $viewTemplate = $viewTemplate->withDescription($view['stream']['description']);
                }
                $included = ($view['stream']['attribute_keys']['included'] ?? []) ?: null;
                $excluded = ($view['stream']['attribute_keys']['excluded'] ?? []) ?: null;
                if ($included !== null || $excluded !== null) {
                    $viewTemplate = $viewTemplate->withAttributeKeyFilter($included, $excluded);
                }
                if (isset($view['stream']['aggregation'])) {
                    $viewTemplate = $viewTemplate->withAggregationResolver($view['stream']['aggregation']->create($context));
                }
                // TODO apply stream.aggregation_cardinality_limit; no cardinality limit support in SDK/Metrics

                $viewRegistry->register(new AllCriteria($criteria), $viewTemplate);
            }

            $disabled = !$properties['meter_provider']['meter_configurator/development']['default_config']['enabled'];
            $configurator = Configurator::meter()->with(static fn (MeterConfig $config) => $config->setDisabled($disabled), null);
            foreach ($properties['meter_provider']['meter_configurator/development']['meters'] as $meter) {
                $disabled = !$meter['config']['enabled'];
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
                // TODO apply meter_provider.exemplar_filter, see SDK/Metrics/Exemplar/ExemplarFilter
                exemplarFilter: null,
                stalenessHandlerFactory: new NoopStalenessHandlerFactory(),
                configurator: $configurator,
            );

            // </editor-fold>
            $sdkBuilder->setMeterProvider($meterProvider);
        }

        // Self-observability is opt-in, as it is for environment-based configuration. Passing the
        // meter provider to the tracer/logger providers and to components is what turns it on.
        $selfObservabilityMeterProvider = $distributionConfiguration->internalMetricsEnabled ? $meterProvider : null;
        $context = $context->withMeterProvider($selfObservabilityMeterProvider ?? new NoopMeterProvider());

        if (isset($properties['tracer_provider'])) {
            $spanProcessors = [];
            foreach ($properties['tracer_provider']['processors'] as $processor) {
                $spanProcessors[] = $processor->create($context);
            }

            $disabled = !$properties['tracer_provider']['tracer_configurator/development']['default_config']['enabled'];
            $configurator = Configurator::tracer()->with(static fn (TracerConfig $config) => $config->setDisabled($disabled), null);

            foreach ($properties['tracer_provider']['tracer_configurator/development']['tracers'] as $tracer) {
                $disabled = !$tracer['config']['enabled'];
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
                        attributeCountLimit: $properties['tracer_provider']['limits']['event_attribute_count_limit'],
                        attributeValueLengthLimit: $properties['tracer_provider']['limits']['attribute_value_length_limit']
                            ?? $properties['attribute_limits']['attribute_value_length_limit'],
                    ),
                    linkAttributesFactory: Attributes::factory(
                        attributeCountLimit: $properties['tracer_provider']['limits']['link_attribute_count_limit'],
                        attributeValueLengthLimit: $properties['tracer_provider']['limits']['attribute_value_length_limit']
                            ?? $properties['attribute_limits']['attribute_value_length_limit'],
                    ),
                    eventCountLimit: $properties['tracer_provider']['limits']['event_count_limit'],
                    linkCountLimit: $properties['tracer_provider']['limits']['link_count_limit'],
                ),
                configurator: $configurator,
                spanSuppressionStrategy: $distributionConfiguration->spanSuppressionStrategy,
                meterProvider: $selfObservabilityMeterProvider,
            );

            // </editor-fold>
            $sdkBuilder->setTracerProvider($tracerProvider);
        }

        if (isset($properties['logger_provider'])) {
            // <editor-fold desc="logger_provider">

            $logRecordProcessors = [];
            foreach ($properties['logger_provider']['processors'] as $processor) {
                $logRecordProcessors[] = $processor->create($context);
            }

            $disabled = !$properties['logger_provider']['logger_configurator/development']['default_config']['enabled'];
            $configurator = Configurator::logger()->with(static fn (LoggerConfig $config) => $config->setDisabled($disabled), null);
            foreach ($properties['logger_provider']['logger_configurator/development']['loggers'] as $logger) {
                $disabled = !$logger['config']['enabled'];
                $configurator = $configurator->with(
                    static fn (LoggerConfig $config) => $config->setDisabled($disabled),
                    name: $logger['name'],
                );
            }

            // TODO apply logger_provider.limits; needs LoggerProvider to accept a LogRecordLimits,
            // which currently hardcodes LogRecordLimitsBuilder (env vars only)
            $loggerProvider = new LoggerProvider(
                processor: new MultiLogRecordProcessor($logRecordProcessors),
                instrumentationScopeFactory: new InstrumentationScopeFactory(Attributes::factory()),
                resource: $resource,
                configurator: $configurator,
                meterProvider: $selfObservabilityMeterProvider,
            );
            $eventLoggerProvider = new EventLoggerProvider($loggerProvider);

            // </editor-fold>
            $sdkBuilder->setLoggerProvider($loggerProvider);
            $sdkBuilder->setEventLoggerProvider($eventLoggerProvider);
        }

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
                    ->example('1.0')
                    ->validate()
                        ->ifTrue(static fn (mixed $v): bool => is_int($v) || is_float($v))
                        ->then(static fn (int|float $v) => throw new \InvalidArgumentException(sprintf(
                            'must be a quoted string, such as "1.0"; got the YAML number %s',
                            var_export($v, true),
                        )))
                    ->end()
                    ->validate()->always(Validation::ensureString())->end()
                    ->validate()->always(self::ensureSupportedFileFormat())->end()
                ->end()
                ->booleanNode('disabled')->defaultFalse()->end()
                ->enumNode('log_level')->values(self::severityNumbers())->defaultNull()->end()
                ->append($this->getResourceConfig($registry, $builder))
                ->append($this->getAttributeLimitsConfig($builder))
                ->append($this->getPropagatorConfig($registry, $builder))
                ->append($this->getTracerProviderConfig($registry, $builder))
                ->append($this->getMeterProviderConfig($registry, $builder))
                ->append($this->getLoggerProviderConfig($registry, $builder))
                ->append($this->getExperimentalResponsePropagatorConfig($registry, $builder))
                // Deliberately laxer than `$defs.Distribution`, which sets `minProperties: 1`: an
                // empty map is accepted, which is also what a map of nothing but foreign keys
                // normalizes to.
                ->append(
                    $registry->componentMap('distribution', DistributionConfiguration::class)
                        ->attribute(IgnoresUnknownProviders::ATTRIBUTE, true)
                        ->defaultValue([])
                )
            ->end();

        return $node;
    }

    /**
     * Applies the `file_format` compatibility rules from upstream's versioning policy: a matching
     * `MAJOR` is required. Anything else is rejected, including the `1.0-rc.*` release candidates
     * and the superseded `0.x` formats. A newer `MINOR` is accepted, and {@see createPlugin()} warns.
     *
     * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/VERSIONING.md#file-format
     */
    private static function ensureSupportedFileFormat(): \Closure
    {
        return static function (?string $value): ?string {
            if ($value === null) {
                return null;
            }
            if (!preg_match('/^(\d+)\.(\d+)$/', $value, $matches)) {
                throw new \InvalidArgumentException(sprintf(
                    'unsupported version "%s", expected MAJOR.MINOR',
                    $value,
                ));
            }

            if ((int) $matches[1] !== self::FILE_FORMAT_MAJOR) {
                throw new \InvalidArgumentException(sprintf(
                    'unsupported version "%s", this SDK implements file_format %d.x',
                    $value,
                    self::FILE_FORMAT_MAJOR,
                ));
            }

            return $value;
        };
    }

    private function getResourceConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('resource');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('attributes')
                    ->requiresAtLeastOneElement()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->cannotBeEmpty()->validate()->always(Validation::ensureString())->end()->end()
                            ->variableNode('value')->isRequired()->end()
                            ->enumNode('type')->defaultNull()
                                ->values(['string', 'bool', 'int', 'double', 'string_array', 'bool_array', 'int_array', 'double_array'])
                            ->end()
                        ->end()
                        ->validate()->always(static function (array $attribute): array {
                            $attribute['value'] = AttributesParser::applyType($attribute['value'], $attribute['type']);

                            return $attribute;
                        })->end()
                    ->end()
                ->end()
                ->scalarNode('attributes_list')->defaultNull()->validate()->always(Validation::ensureKeyValueList())->end()->end()
                ->arrayNode('detection/development')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('attributes')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('included')
                                    ->defaultNull()
                                    ->requiresAtLeastOneElement()
                                    ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                                ->end()
                                ->arrayNode('excluded')
                                    ->requiresAtLeastOneElement()
                                    ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                                ->end()
                            ->end()
                        ->end()
                        // Unknown detectors warn and are skipped rather than failing startup, so
                        // that a portable config may name detectors another SDK provides.
                        ->append(
                            $registry->componentList('detectors', ResourceDetectorInterface::class)
                                ->requiresAtLeastOneElement()
                                ->attribute(IgnoresUnknownProviders::ATTRIBUTE, true)
                        )
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
            ->validate()
                ->ifTrue(static fn (?array $v): bool => ($v['processors'] ?? []) === [])
                ->thenInvalid('A tracer provider needs at least one processor. Omit tracer_provider for a noop.')
            ->end()
            ->children()
                ->arrayNode('limits')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('attribute_value_length_limit')->min(0)->defaultNull()->end()
                        ->integerNode('attribute_count_limit')->min(0)->defaultNull()->end()
                        ->integerNode('event_count_limit')->min(0)->defaultValue(128)->end()
                        ->integerNode('link_count_limit')->min(0)->defaultValue(128)->end()
                        ->integerNode('event_attribute_count_limit')->min(0)->defaultValue(128)->end()
                        ->integerNode('link_attribute_count_limit')->min(0)->defaultValue(128)->end()
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
                                ->booleanNode('enabled')->defaultTrue()->end()
                            ->end()
                        ->end()
                        ->arrayNode('tracers')
                            ->requiresAtLeastOneElement()
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->isRequired()->validate()->always(Validation::ensureString())->end()->end()
                                    ->arrayNode('config')
                                        ->isRequired()
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->booleanNode('enabled')->defaultTrue()->end()
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
            ->validate()
                ->ifTrue(static fn (?array $v): bool => ($v['readers'] ?? []) === [])
                ->thenInvalid('A meter provider needs at least one reader. Omit meter_provider for a noop.')
            ->end()
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
                    ->requiresAtLeastOneElement()
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('stream')
                                ->isRequired()
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('name')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                                    ->scalarNode('description')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                                    ->integerNode('aggregation_cardinality_limit')->defaultNull()->min(1)->end()
                                    ->arrayNode('attribute_keys')
                                        ->children()
                                            ->arrayNode('included')
                                                ->requiresAtLeastOneElement()
                                                ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                                            ->end()
                                            ->arrayNode('excluded')
                                                ->requiresAtLeastOneElement()
                                                ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->append($registry->component('aggregation', DefaultAggregationProviderInterface::class))
                                ->end()
                            ->end()
                            ->arrayNode('selector')
                                ->isRequired()
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
                ->append($registry->componentList('readers', MetricReaderInterface::class))
                ->arrayNode('meter_configurator/development')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('default_config')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                            ->end()
                        ->end()
                        ->arrayNode('meters')
                            ->requiresAtLeastOneElement()
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->isRequired()->validate()->always(Validation::ensureString())->end()->end()
                                    ->arrayNode('config')
                                        ->isRequired()
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->booleanNode('enabled')->defaultTrue()->end()
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
            ->validate()
                ->ifTrue(static fn (?array $v): bool => ($v['processors'] ?? []) === [])
                ->thenInvalid('A logger provider needs at least one processor. Omit logger_provider for a noop.')
            ->end()
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
                        ->append($this->getLoggerConfig($builder, 'default_config'))
                        ->arrayNode('loggers')
                            ->requiresAtLeastOneElement()
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->isRequired()->validate()->always(Validation::ensureString())->end()->end()
                                    ->append($this->getLoggerConfig($builder, 'config')->isRequired())
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
     * `ExperimentalLoggerConfig`. Unlike the tracer and meter equivalents this carries
     * `minimum_severity` and `trace_based`; both are accepted so that valid 1.0 files
     * parse, but are not yet acted on.
     */
    private function getLoggerConfig(NodeBuilder $builder, string $name): ArrayNodeDefinition
    {
        $node = $builder->arrayNode($name);
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                // TODO apply severity and trace based log record filtering
                ->enumNode('minimum_severity')->values(self::severityNumbers())->defaultNull()->end()
                ->booleanNode('trace_based')->defaultNull()->end()
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
                    // Left for type validation to reject.
                    if (!is_array($value['composite'] ?? []) || !is_string($value['composite_list'] ?? '')) {
                        return $value;
                    }
                    $existing = [];
                    foreach ($value['composite'] ?? [] as $item) {
                        if (is_array($item)) {
                            $existing[] = key($item);
                        }
                    }
                    foreach (explode(',', $value['composite_list'] ?? '') as $name) {
                        $name = trim($name);
                        if ($name !== '' && $name !== 'none' && !in_array($name, $existing)) {
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
                ->scalarNode('composite_list')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                ->append($registry->componentList('composite', TextMapPropagatorInterface::class)->requiresAtLeastOneElement())
            ->end()
        ;

        return $node;
    }

    /**
     * Deprecated in favor of `distribution.opentelemetry_php/development.response_propagator/development`.
     * It has no defaults, so it is absent unless the file sets it.
     */
    private function getExperimentalResponsePropagatorConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('response_propagator/development');
        $node
            ->beforeNormalization()
            ->ifArray()
            ->then(static function (array $value): array {
                // Left for type validation to reject.
                if (!is_array($value['composite'] ?? []) || !is_string($value['composite_list'] ?? '')) {
                    return $value;
                }
                $existing = [];
                foreach ($value['composite'] ?? [] as $item) {
                    if (is_array($item)) {
                        $existing[] = key($item);
                    }
                }
                foreach (explode(',', $value['composite_list'] ?? '') as $name) {
                    $name = trim($name);
                    if ($name !== '' && $name !== 'none' && !in_array($name, $existing)) {
                        $value['composite'][][$name] = null;
                        $existing[] = $name;
                    }
                }

                unset($value['composite_list']);

                return $value;
            })
            ->end();

        $node
            ->children()
            ->scalarNode('composite_list')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
            ->append($registry->componentList('composite', ResponsePropagatorInterface::class))
            ->end()
        ;

        return $node;
    }
}
