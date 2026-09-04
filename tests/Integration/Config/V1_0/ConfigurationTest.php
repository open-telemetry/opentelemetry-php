<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config\V1_0;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\OpenTelemetrySdk;
use OpenTelemetry\Config\SDK\ComponentProvider\OutputStreamParser;
use OpenTelemetry\Config\SDK\Configuration;
use OpenTelemetry\Config\SDK\Configuration\ConfigurationFactory;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\Tests\Integration\Config\ComponentProvider\Detector\ServiceName;
use org\bovigo\vfs\vfsStream;
use Override;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Integration tests for file_format "1.0-rc.2" configuration.
 *
 * Covers the full kitchen-sink config, version-specific inline-YAML behavior tests,
 * and any features or field shapes specific to the 1.0-rc.2 schema.
 */
#[CoversNothing]
final class ConfigurationTest extends TestCase
{
    #[\Override]
    public function setUp(): void
    {
        $root = vfsStream::setup('/', null, ['var' => ['log' => []]])->url();
        OutputStreamParser::setRoot($root);
    }

    #[\Override]
    public function tearDown(): void
    {
        OutputStreamParser::reset();
    }

    // -------------------------------------------------------------------------
    // Smoke tests: parse + create without errors
    // -------------------------------------------------------------------------

    #[DataProvider('openTelemetryConfigurationDataProvider')]
    public function test_open_telemetry_configuration(string $file): void
    {
        $this->expectNotToPerformAssertions();
        Configuration::parseFile($file)->create();
    }

    public static function openTelemetryConfigurationDataProvider(): iterable
    {
        yield 'kitchen-sink' => [__DIR__ . '/configurations/kitchen-sink.yaml'];
        yield 'anchors'      => [__DIR__ . '/../configurations/anchors.yaml'];
        yield 'php-specific' => [__DIR__ . '/../configurations/php-specific.yaml'];
    }

    // -------------------------------------------------------------------------
    // Resource priority tests (inline YAML, file_format "1.0-rc.2")
    // -------------------------------------------------------------------------

    public function test_resource_attributes_take_precedence_over_default_attributes(): void
    {
        $factory = new ConfigurationFactory(
            [],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0-rc.2"
            resource:
              attributes:
              - { name: service.name, value: test-service }
            YAML)]);
        $resource = $this->getResource($sdk->create(new Context())->build());

        $this->assertSame('test-service', $resource->getAttributes()->get('service.name'));
    }

    public function test_resource_detectors_take_precedence_over_default_attributes(): void
    {
        $factory = new ConfigurationFactory(
            [new ServiceName('test-service')],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0-rc.2"
            resource:
              detection/development:
                detectors:
                - service_name:
            YAML)]);
        $resource = $this->getResource($sdk->create(new Context())->build());

        $this->assertSame('test-service', $resource->getAttributes()->get('service.name'));
    }

    #[Depends('test_resource_attributes_take_precedence_over_default_attributes')]
    #[Depends('test_resource_detectors_take_precedence_over_default_attributes')]
    public function test_resource_attributes_take_precedence_over_resource_detectors(): void
    {
        $factory = new ConfigurationFactory(
            [new ServiceName('should-be-overridden')],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0-rc.2"
            resource:
              attributes:
              - { name: service.name, value: test-service }
              detection/development:
                detectors:
                - service_name:
            YAML)]);
        $resource = $this->getResource($sdk->create(new Context())->build());

        $this->assertSame('test-service', $resource->getAttributes()->get('service.name'));
    }

    // -------------------------------------------------------------------------
    // Extension point test (inline YAML, file_format "1.0-rc.2")
    // -------------------------------------------------------------------------

    public function test_samplers_have_access_to_resource_info_extension(): void
    {
        $samplerProvider = new /** @implements ComponentProvider<SamplerInterface> */ class() implements ComponentProvider {
            public ?string $serviceName = null;

            #[Override]
            public function createPlugin(array $properties, Context $context): SamplerInterface
            {
                $this->serviceName = $context->getExtension(ResourceInfo::class)?->getAttributes()->get('service.name');

                return new AlwaysOnSampler();
            }

            #[Override]
            public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
            {
                return $builder->arrayNode('remote_sampler');
            }
        };

        $factory = new ConfigurationFactory(
            [$samplerProvider],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0-rc.2"
            resource:
              attributes:
              - { name: service.name, value: test-service }
            tracer_provider:
              sampler:
                remote_sampler:
            YAML)]);
        $sdk->create(new Context());

        $this->assertSame('test-service', $samplerProvider->serviceName);
    }

    // -------------------------------------------------------------------------
    // enabled: guard — must be rejected in file_format "1.0-rc.2"
    // -------------------------------------------------------------------------

    /**
     * Using the 1.1 `enabled:` field in a 1.0-rc.2 file must throw immediately
     * so that users get a clear error message rather than silent misbehaviour.
     */
    public function test_enabled_field_rejected_in_v1_0(): void
    {
        $factory = new ConfigurationFactory(
            [],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/"1\.0-rc\.2"/');

        $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0-rc.2"
            tracer_provider:
              tracer_configurator/development:
                default_config:
                  enabled: true
            YAML)]);
    }

    // -------------------------------------------------------------------------
    // 1.1-only field guards — must be rejected in file_format "1.0-rc.2"
    // -------------------------------------------------------------------------

    /**
     * @param non-empty-string $yamlFragment inline YAML containing the forbidden field
     * @param non-empty-string $fieldPath dotted path used in the expected error message
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('v11OnlyFieldsProvider')]
    public function test_v11_only_fields_rejected_in_v1_0(string $yamlFragment, string $fieldPath): void
    {
        $factory = new ConfigurationFactory(
            [],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/"1\.0-rc\.2"/');
        $this->expectExceptionMessageMatches('/' . preg_quote($fieldPath, '/') . '/');

        $factory->process([Yaml::parse($yamlFragment)]);
    }

    public static function v11OnlyFieldsProvider(): iterable
    {
        yield 'attribute_limits.attribute_value_depth_limit' => [
            /** @lang yaml */<<<'YAML'
            file_format: "1.0-rc.2"
            attribute_limits:
              attribute_value_depth_limit: 32
            YAML,
            'attribute_limits.attribute_value_depth_limit',
        ];

        yield 'tracer_provider.limits.attribute_value_depth_limit' => [
            /** @lang yaml */<<<'YAML'
            file_format: "1.0-rc.2"
            tracer_provider:
              limits:
                attribute_value_depth_limit: 32
            YAML,
            'tracer_provider.limits.attribute_value_depth_limit',
        ];

        yield 'logger_provider.limits.attribute_value_depth_limit' => [
            /** @lang yaml */<<<'YAML'
            file_format: "1.0-rc.2"
            logger_provider:
              limits:
                attribute_value_depth_limit: 32
            YAML,
            'logger_provider.limits.attribute_value_depth_limit',
        ];

        yield 'tracer_provider.id_generator' => [
            /** @lang yaml */<<<'YAML'
            file_format: "1.0-rc.2"
            tracer_provider:
              id_generator:
                random: {}
            YAML,
            'tracer_provider.id_generator',
        ];
    }

    // -------------------------------------------------------------------------
    // TLS backward-compatibility — flat 1.0-rc.2 fields must still parse
    // -------------------------------------------------------------------------

    /**
     * The old flat TLS fields (certificate_file, client_key_file,
     * client_certificate_file, insecure) that were the only option in
     * file_format "1.0-rc.2" must continue to be accepted for all OTLP
     * exporters after the migration to the `tls:` sub-object in 1.1.
     */
    public function test_flat_tls_fields_still_work_in_v1_0(): void
    {
        $this->expectNotToPerformAssertions();
        Configuration::parseFile(__DIR__ . '/configurations/tls-backward-compat.yaml')->create();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function getResource(Sdk $sdk): ResourceInfo
    {
        $tracer = $sdk->getTracerProvider()->getTracer('test');

        $tracerReflection = new \ReflectionClass($tracer);
        $sharedStateProperty = $tracerReflection->getProperty('tracerSharedState');
        $sharedStateProperty->setAccessible(true);
        $sharedState = $sharedStateProperty->getValue($tracer);

        $stateReflection = new \ReflectionClass($sharedState);
        $resourceProperty = $stateReflection->getProperty('resource');
        $resourceProperty->setAccessible(true);

        return $resourceProperty->getValue($sharedState);
    }
}
