<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config\V1_1;

use OpenTelemetry\Config\SDK\ComponentProvider\OpenTelemetrySdk;
use OpenTelemetry\Config\SDK\ComponentProvider\OutputStreamParser;
use OpenTelemetry\Config\SDK\Configuration;
use OpenTelemetry\Config\SDK\Configuration\ConfigurationFactory;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LoggerSharedState;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\Trace\RandomIdGenerator;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysRecordSampler;
use OpenTelemetry\SDK\Trace\TracerProvider;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Integration tests for file_format "1.1" configuration.
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
    }

    // -------------------------------------------------------------------------
    // Group 6: enabled: field in configurators (replaces disabled:)
    // -------------------------------------------------------------------------

    /**
     * In file_format "1.1" the `enabled:` field replaces `disabled:` in all
     * configurator blocks. Verify that the SDK honours the new field correctly.
     */
    public function test_enabled_field_enables_and_disables_providers(): void
    {
        $sdk = Configuration::parseFile(
            __DIR__ . '/configurations/configurators-enabled-field.yaml',
        )->create()->build();

        $tracerProvider = $sdk->getTracerProvider();
        $this->assertTrue($tracerProvider->getTracer('enabled-tracer')->isEnabled(), 'explicitly enabled');
        $this->assertFalse($tracerProvider->getTracer('disabled-tracer')->isEnabled(), 'explicitly disabled');
        $this->assertFalse($tracerProvider->getTracer('other-tracer')->isEnabled(), 'default disabled');
    }

    /**
     * Using the old `disabled:` field in file_format "1.1" must emit an
     * E_USER_DEPRECATED notice so users know to migrate to `enabled:`.
     */
    public function test_disabled_field_triggers_deprecation_in_v1_1(): void
    {
        $deprecations = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$deprecations): bool {
            if ($errno === E_USER_DEPRECATED) {
                $deprecations[] = $errstr;
            }

            return true;
        }, E_USER_DEPRECATED);

        try {
            $factory = new ConfigurationFactory(
                [],
                new OpenTelemetrySdk(),
                new EnvSourceReader([]),
            );

            $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
                file_format: "1.1"
                tracer_provider:
                  tracer_configurator/development:
                    default_config:
                      disabled: true
                YAML)]);
        } finally {
            restore_error_handler();
        }

        $this->assertNotEmpty($deprecations, 'At least one deprecation must be emitted');
        $this->assertMatchesRegularExpression('/"1\.1"/', $deprecations[0]);
    }

    // -------------------------------------------------------------------------
    // Group 1.2: exemplar_filter wiring
    // -------------------------------------------------------------------------

    #[DataProvider('exemplarFilterProvider')]
    public function test_exemplar_filter_is_wired_correctly(string $file, string $expectedClass): void
    {
        $sdk = Configuration::parseFile($file)->create()->build();

        $filter = $this->getExemplarFilter($sdk->getMeterProvider());
        $this->assertInstanceOf($expectedClass, $filter);
    }

    public static function exemplarFilterProvider(): iterable
    {
        $dir = __DIR__ . '/configurations';
        yield 'trace_based' => ["{$dir}/exemplar-filter-trace-based.yaml", WithSampledTraceExemplarFilter::class];
        yield 'always_on'   => ["{$dir}/exemplar-filter-always-on.yaml",   AllExemplarFilter::class];
        yield 'always_off'  => ["{$dir}/exemplar-filter-always-off.yaml",  NoneExemplarFilter::class];
    }

    // -------------------------------------------------------------------------
    // Group 7: logger_provider.limits applied to LogRecordLimits
    // -------------------------------------------------------------------------

    public function test_logger_provider_limits_are_applied(): void
    {
        $sdk = Configuration::parseFile(
            __DIR__ . '/configurations/logger-provider-limits.yaml',
        )->create()->build();

        $limits = $this->getLogRecordLimits($sdk->getLoggerProvider());
        $attributeFactory = $limits->getAttributeFactory();

        // Build attributes that exercise both limits:
        //   attribute_count_limit: 2  →  third attribute is dropped
        //   attribute_value_length_limit: 5  →  long values are trimmed
        $attributes = $attributeFactory->builder([
            'key1' => 'hello',     // allowed — value ≤ 5 chars
            'key2' => 'toolong',   // trimmed  — value > 5 chars
            'key3' => 'dropped',   // dropped  — exceeds count limit of 2
        ])->build();

        $this->assertCount(2, $attributes, 'attribute_count_limit=2 must drop the third attribute');
        $this->assertSame(1, $attributes->getDroppedAttributesCount());
        $this->assertSame('hello', $attributes->get('key1'));
        $this->assertSame('toolo', $attributes->get('key2'), 'value must be trimmed to 5 chars');
    }

    // -------------------------------------------------------------------------
    // Group 8.1: always_record sampler
    // -------------------------------------------------------------------------

    public function test_always_record_sampler_is_wired_in_tracer_provider(): void
    {
        $sdk = Configuration::parseFile(
            __DIR__ . '/configurations/kitchen-sink.yaml',
        )->create()->build();

        $tracerProvider = $sdk->getTracerProvider();
        assert($tracerProvider instanceof TracerProvider);
        $this->assertInstanceOf(AlwaysRecordSampler::class, $tracerProvider->getSampler());
    }

    // -------------------------------------------------------------------------
    // Group 8.2: id_generator: random
    // -------------------------------------------------------------------------

    public function test_random_id_generator_is_wired_in_tracer_provider(): void
    {
        $sdk = Configuration::parseFile(
            __DIR__ . '/configurations/kitchen-sink.yaml',
        )->create()->build();

        $idGenerator = $this->getIdGenerator($sdk);
        $this->assertInstanceOf(RandomIdGenerator::class, $idGenerator);
    }

    // -------------------------------------------------------------------------
    // Group 4: tls: sub-object accepted for OTLP HTTP exporters
    // -------------------------------------------------------------------------

    public function test_tls_sub_object_is_accepted_for_otlp_http_exporters(): void
    {
        $this->expectNotToPerformAssertions();
        Configuration::parseFile(__DIR__ . '/configurations/tls-sub-object.yaml')->create();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function getExemplarFilter(\OpenTelemetry\API\Metrics\MeterProviderInterface $meterProvider): ExemplarFilterInterface
    {
        assert($meterProvider instanceof MeterProvider);
        $reflection = new \ReflectionClass($meterProvider);
        $property = $reflection->getProperty('exemplarFilter');
        $property->setAccessible(true);

        return $property->getValue($meterProvider);
    }

    private function getLogRecordLimits(\OpenTelemetry\API\Logs\LoggerProviderInterface $loggerProvider): \OpenTelemetry\SDK\Logs\LogRecordLimits
    {
        assert($loggerProvider instanceof LoggerProvider);
        $providerReflection = new \ReflectionClass($loggerProvider);
        $sharedStateProperty = $providerReflection->getProperty('loggerSharedState');
        $sharedStateProperty->setAccessible(true);
        $sharedState = $sharedStateProperty->getValue($loggerProvider);

        assert($sharedState instanceof LoggerSharedState);

        return $sharedState->getLogRecordLimits();
    }

    private function getIdGenerator(Sdk $sdk): \OpenTelemetry\SDK\Trace\IdGeneratorInterface
    {
        $tracerProvider = $sdk->getTracerProvider();
        assert($tracerProvider instanceof TracerProvider);

        $tracer = $tracerProvider->getTracer('test');
        $tracerReflection = new \ReflectionClass($tracer);
        $sharedStateProperty = $tracerReflection->getProperty('tracerSharedState');
        $sharedStateProperty->setAccessible(true);
        $sharedState = $sharedStateProperty->getValue($tracer);

        return $sharedState->getIdGenerator();
    }
}
