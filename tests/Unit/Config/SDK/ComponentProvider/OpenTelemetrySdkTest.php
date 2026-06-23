<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\ComponentProvider;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\OpenTelemetrySdk;
use OpenTelemetry\Context\Propagation\NoopResponsePropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\SdkBuilder;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(OpenTelemetrySdk::class)]
final class OpenTelemetrySdkTest extends TestCase
{
    public function test_get_config_returns_array_node_definition(): void
    {
        $provider = new OpenTelemetrySdk();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $registry->method('component')->willReturn(new ArrayNodeDefinition('test'));
        $registry->method('componentList')->willReturn(new ArrayNodeDefinition('test'));

        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin_disabled(): void
    {
        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(disabled: true);
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_no_propagators(): void
    {
        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(disabled: true);
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_propagators(): void
    {
        $mockPropagator = $this->createMock(TextMapPropagatorInterface::class);
        $propagatorPlugin = $this->createMock(ComponentPlugin::class);
        $propagatorPlugin->method('create')->willReturn($mockPropagator);

        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(
            disabled: true,
            propagatorPlugins: [$propagatorPlugin],
        );
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_response_propagators(): void
    {
        $mockPropagator = $this->createMock(ResponsePropagatorInterface::class);
        $propagatorPlugin = $this->createMock(ComponentPlugin::class);
        $propagatorPlugin->method('create')->willReturn($mockPropagator);

        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(
            disabled: true,
            responsePropagatorPlugins: [$propagatorPlugin],
        );
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_enabled_minimal(): void
    {
        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(disabled: false);
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_span_processors(): void
    {
        $mockProcessor = $this->createMock(SpanProcessorInterface::class);
        $mockProcessor->method('forceFlush')->willReturn(true);
        $mockProcessor->method('shutdown')->willReturn(true);
        $processorPlugin = $this->createMock(ComponentPlugin::class);
        $processorPlugin->method('create')->willReturn($mockProcessor);

        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(
            disabled: false,
            spanProcessorPlugins: [$processorPlugin],
        );
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_sampler(): void
    {
        $mockSampler = $this->createMock(SamplerInterface::class);
        $samplerPlugin = $this->createMock(ComponentPlugin::class);
        $samplerPlugin->method('create')->willReturn($mockSampler);

        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(
            disabled: false,
            samplerPlugin: $samplerPlugin,
        );
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_tracer_configurator(): void
    {
        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(
            disabled: false,
            tracerConfigurator: [
                'default_config' => ['disabled' => true],
                'tracers' => [
                    ['name' => 'my-tracer', 'config' => ['disabled' => false]],
                ],
            ],
        );
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_meter_views(): void
    {
        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(
            disabled: false,
            views: [
                [
                    'stream' => [
                        'name' => 'my-view',
                        'description' => 'desc',
                        'aggregation_cardinality_limit' => null,
                        'attribute_keys' => ['included' => ['key1'], 'excluded' => []],
                        'aggregation' => null,
                    ],
                    'selector' => [
                        'instrument_type' => 'counter',
                        'instrument_name' => 'my.instrument',
                        'unit' => 'ms',
                        'meter_name' => 'my-meter',
                        'meter_version' => '1.0.0',
                        'meter_schema_url' => 'https://example.com',
                    ],
                ],
            ],
        );
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_all_instrument_types(): void
    {
        $types = ['counter', 'histogram', 'observable_counter', 'observable_gauge', 'observable_up_down_counter', 'up_down_counter'];

        foreach ($types as $type) {
            $provider = new OpenTelemetrySdk();
            $properties = $this->createProperties(
                disabled: false,
                views: [
                    [
                        'stream' => [
                            'name' => null,
                            'description' => null,
                            'aggregation_cardinality_limit' => null,
                            'attribute_keys' => ['included' => null, 'excluded' => []],
                        ],
                        'selector' => [
                            'instrument_type' => $type,
                            'instrument_name' => null,
                            'unit' => null,
                            'meter_name' => null,
                            'meter_version' => null,
                            'meter_schema_url' => null,
                        ],
                    ],
                ],
            );
            $result = $provider->createPlugin($properties, new Context());
            $this->assertInstanceOf(SdkBuilder::class, $result, "Failed for instrument type: $type");
        }
    }

    public function test_create_plugin_with_meter_configurator(): void
    {
        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(
            disabled: false,
            meterConfigurator: [
                'default_config' => ['disabled' => false],
                'meters' => [
                    ['name' => 'my-meter', 'config' => ['disabled' => true]],
                ],
            ],
        );
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_log_processors(): void
    {
        $mockProcessor = $this->createMock(LogRecordProcessorInterface::class);
        $processorPlugin = $this->createMock(ComponentPlugin::class);
        $processorPlugin->method('create')->willReturn($mockProcessor);

        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(
            disabled: false,
            logProcessorPlugins: [$processorPlugin],
        );
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_logger_configurator(): void
    {
        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(
            disabled: false,
            loggerConfigurator: [
                'default_config' => ['disabled' => true],
                'loggers' => [
                    ['name' => 'my-logger', 'config' => ['disabled' => false]],
                ],
            ],
        );
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_resource_detectors(): void
    {
        $mockDetector = $this->createMock(ResourceDetectorInterface::class);
        $mockDetector->method('getResource')->willReturn(
            \OpenTelemetry\SDK\Resource\ResourceInfo::create(\OpenTelemetry\SDK\Common\Attribute\Attributes::create([]))
        );
        $detectorPlugin = $this->createMock(ComponentPlugin::class);
        $detectorPlugin->method('create')->willReturn($mockDetector);

        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(
            disabled: false,
            resourceDetectorPlugins: [$detectorPlugin],
            resourceIncluded: ['key1'],
            resourceExcluded: ['key2'],
        );
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_resource_schema_url(): void
    {
        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(
            disabled: false,
            schemaUrl: 'https://example.com/schema',
        );
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_resource_attributes(): void
    {
        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(
            disabled: false,
            resourceAttributes: [
                ['name' => 'service.name', 'value' => 'my-service', 'type' => 'string'],
            ],
        );
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_attributes_list(): void
    {
        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(
            disabled: false,
            attributesList: 'service.name=my-service,service.version=1.0',
        );
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_metric_readers(): void
    {
        $mockReader = $this->createMock(MetricReaderInterface::class);
        $readerPlugin = $this->createMock(ComponentPlugin::class);
        $readerPlugin->method('create')->willReturn($mockReader);

        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(
            disabled: false,
            metricReaderPlugins: [$readerPlugin],
        );
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    public function test_create_plugin_with_tracer_attribute_limits(): void
    {
        $provider = new OpenTelemetrySdk();
        $properties = $this->createProperties(
            disabled: false,
            tracerAttributeCountLimit: 64,
            tracerAttributeValueLengthLimit: 256,
            eventAttributeCountLimit: 32,
            linkAttributeCountLimit: 16,
        );
        $result = $provider->createPlugin($properties, new Context());

        $this->assertInstanceOf(SdkBuilder::class, $result);
    }

    private function createProperties(
        bool $disabled = true,
        array $propagatorPlugins = [],
        array $responsePropagatorPlugins = [],
        array $spanProcessorPlugins = [],
        ?ComponentPlugin $samplerPlugin = null,
        array $tracerConfigurator = null,
        array $views = [],
        array $metricReaderPlugins = [],
        array $meterConfigurator = null,
        array $logProcessorPlugins = [],
        array $loggerConfigurator = null,
        array $resourceDetectorPlugins = [],
        ?array $resourceIncluded = null,
        array $resourceExcluded = [],
        ?string $schemaUrl = null,
        array $resourceAttributes = [],
        ?string $attributesList = null,
        ?int $tracerAttributeCountLimit = null,
        ?int $tracerAttributeValueLengthLimit = null,
        ?int $eventAttributeCountLimit = null,
        ?int $linkAttributeCountLimit = null,
    ): array {
        return [
            'file_format' => '1.0-rc.2',
            'disabled' => $disabled,
            'resource' => [
                'attributes' => $resourceAttributes,
                'attributes_list' => $attributesList,
                'detection/development' => [
                    'attributes' => [
                        'included' => $resourceIncluded,
                        'excluded' => $resourceExcluded,
                    ],
                    'detectors' => $resourceDetectorPlugins,
                ],
                'schema_url' => $schemaUrl,
            ],
            'attribute_limits' => [
                'attribute_value_length_limit' => null,
                'attribute_count_limit' => 128,
            ],
            'propagator' => [
                'composite' => $propagatorPlugins,
            ],
            'response_propagator/development' => [
                'composite' => $responsePropagatorPlugins,
            ],
            'tracer_provider' => [
                'limits' => [
                    'attribute_value_length_limit' => $tracerAttributeValueLengthLimit,
                    'attribute_count_limit' => $tracerAttributeCountLimit,
                    'event_count_limit' => 128,
                    'link_count_limit' => 128,
                    'event_attribute_count_limit' => $eventAttributeCountLimit,
                    'link_attribute_count_limit' => $linkAttributeCountLimit,
                ],
                'sampler' => $samplerPlugin,
                'processors' => $spanProcessorPlugins,
                'tracer_configurator/development' => $tracerConfigurator,
            ],
            'meter_provider' => [
                'views' => $views,
                'readers' => $metricReaderPlugins,
                'exemplar_filter' => 'trace_based',
                'meter_configurator/development' => $meterConfigurator,
            ],
            'logger_provider' => [
                'limits' => [
                    'attribute_value_length_limit' => null,
                    'attribute_count_limit' => null,
                ],
                'processors' => $logProcessorPlugins,
                'logger_configurator/development' => $loggerConfigurator,
            ],
        ];
    }
}
