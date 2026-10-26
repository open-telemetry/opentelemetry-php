<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Baggage\Propagation\BaggagePropagator;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\Configuration\General\HttpConfig;
use OpenTelemetry\API\Metrics\Noop\NoopCounter;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Config\SDK\ComponentProvider\InstrumentationConfigurationRegistry;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterConsole;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricReaderPeriodic;
use OpenTelemetry\Config\SDK\ComponentProvider\OpenTelemetrySdk;
use OpenTelemetry\Config\SDK\ComponentProvider\OutputStreamParser;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterConsole;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanProcessorSimple;
use OpenTelemetry\Config\SDK\Configuration;
use OpenTelemetry\Config\SDK\Configuration\ConfigurationFactory;
use OpenTelemetry\Config\SDK\Configuration\Environment\ArrayEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use OpenTelemetry\Config\SDK\Configuration\Internal\SkippedComponentPlugin;
use OpenTelemetry\Config\SDK\Instrumentation;
use OpenTelemetry\Context\Propagation\MultiResponsePropagator;
use OpenTelemetry\Context\Propagation\NoopResponsePropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Extension\Propagator\B3\B3Propagator;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;
use OpenTelemetry\SDK\Metrics\Aggregation\DropAggregation;
use OpenTelemetry\SDK\Metrics\Aggregation\ExplicitBucketHistogramAggregation;
use OpenTelemetry\SDK\Metrics\Aggregation\LastValueAggregation;
use OpenTelemetry\SDK\Metrics\Aggregation\SumAggregation;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\ViewRegistryInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\CompositeSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppressionStrategy;
use OpenTelemetry\SDK\Trace\SpanSuppression\SemanticConventionSuppressionStrategy\SemanticConventionSuppressionStrategy;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\Tests\Integration\Config\ComponentProvider\Detector\ServiceName;
use OpenTelemetry\Tests\TestState;
use org\bovigo\vfs\vfsStream;
use Override;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Yaml\Yaml;

#[CoversNothing]
final class ConfigurationTest extends TestCase
{
    use TestState;

    #[Override]
    public function setUp(): void
    {
        // set up mock file system with /var/log directory, for otlp_file exporter.
        $root = vfsStream::setup('/', null, ['var' => ['log' => []]])->url();

        OutputStreamParser::setRoot($root);
    }

    #[Override]
    public function tearDown(): void
    {
        OutputStreamParser::reset();
    }

    #[DataProvider('openTelemetryConfigurationDataProvider')]
    public function test_open_telemetry_configuration(string $file): void
    {
        $this->expectNotToPerformAssertions();
        Configuration::parseFile($file)->create();
    }

    #[DataProvider('openTelemetryConfigurationDataProvider')]
    public function test_instrumentation_configuration(string $file): void
    {
        $this->expectNotToPerformAssertions();
        // The upstream kitchen sink has a "php: example" entry with no provider, so it warns.
        Configuration::parseFile($file)->getInstrumentationConfig((new Context())->withLogger(new NullLogger()));
    }

    public function test_one_load_serves_both_roots(): void
    {
        $config = Configuration::parseFile(__DIR__ . '/../../Unit/SDK/fixtures/otel-sdk.yaml');

        $this->assertInstanceOf(TracerProvider::class, $config->create()->build()->getTracerProvider());
        $this->assertInstanceOf(
            HttpConfig::class,
            $config->getInstrumentationConfig()->get(HttpConfig::class),
        );
    }

    /**
     * Reading instrumentation configuration must not disturb what the SDK root sees, and vice
     * versa: both roots are processed from the one parsed document.
     */
    public function test_anchors_are_not_shared_between_roots(): void
    {
        $config = Configuration::parseFile(__DIR__ . '/configurations/anchors.yaml');
        $first = $config->getInstrumentationConfig();
        $config->create();

        $this->assertEquals($first, $config->getInstrumentationConfig());
    }

    /**
     * @phan-suppress PhanDeprecatedClass
     */
    public function test_deprecated_instrumentation_entry_point(): void
    {
        $registry = Instrumentation::parseFile(__DIR__ . '/../../Unit/SDK/fixtures/otel-sdk.yaml')->create();

        $this->assertInstanceOf(HttpConfig::class, $registry->get(HttpConfig::class));
    }

    /**
     * Isolated because SPI registration is global, and {@see Configuration} caches its factories.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_legacy_spi_key_logs_deprecation(): void
    {
        /** @phpstan-ignore-next-line */
        ServiceLoader::register(Configuration\ComponentProvider::class, LegacySpiKeyProvider::class);

        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->once())
            ->method('write')
            ->with(
                LogLevel::WARNING,
                $this->logicalAnd(
                    $this->stringContains(LegacySpiKeyProvider::class),
                    $this->stringContains(ComponentProvider::class),
                ),
                $this->anything(),
            );
        Logging::setLogWriter($logWriter);

        try {
            Configuration::parseFile(__DIR__ . '/configurations/minimal.yaml');
        } finally {
            Logging::reset();
        }
    }

    /**
     * An escaped `$${FOO}` is a request for the literal `${FOO}`, so it must survive into the built
     * SDK unresolved even when the value sits under a YAML alias.
     *
     * Note this exercises whichever backend `parseFile()` selects, which is symfony/yaml wherever
     * that is installed — so it does *not* on its own guard the ext-yaml reference bug this fixture
     * was written for. Keeping it anyway: it pins the user-visible contract through a real built
     * SDK under the backend most installs actually use. The ext-yaml half, where an alias becomes a
     * PHP reference and substitution's write-back could resolve the escape, is guarded by
     * `ConfigurationFactoryTest::test_escaped_substitution_under_yaml_alias_is_not_resolved()`,
     * which drives that loader directly.
     */
    #[RequiresPhpExtension('yaml')]
    public function test_escaped_substitution_under_yaml_alias_survives_into_built_sdk(): void
    {
        $this->setEnvironmentVariable('DO_NOT_REPLACE_ME', 'Never use this value');

        $sdk = Configuration::parseFile(__DIR__ . '/configurations/alias-escaped-substitution.yaml')
            ->create()
            ->build();

        $attributes = $this->getResource($sdk)->getAttributes()->toArray();

        $this->assertSame('${DO_NOT_REPLACE_ME}', $attributes['escaped'] ?? null);
        $this->assertSame('${DO_NOT_REPLACE_ME}', $attributes['escaped_via_alias'] ?? null);
    }

    public function test_unknown_php_instrumentation_is_skipped_with_warning(): void
    {
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->atLeastOnce())->method('write')->with(
            LogLevel::WARNING,
            $this->stringContains('Ignoring "php" entry "not_installed"'),
            $this->anything(),
        );
        Logging::setLogWriter($logWriter);

        try {
            $registry = self::instrumentationFactory()->process([Yaml::parse(<<<'YAML'
                instrumentation/development:
                  php:
                    not_installed:
                      span_name: whatever
                YAML)])->create(new Context());
        } finally {
            Logging::reset();
        }

        $this->assertNull($registry->get(HttpConfig::class));
    }

    public function test_unknown_general_key_is_dropped(): void
    {
        $registry = self::instrumentationFactory()->process([Yaml::parse(<<<'YAML'
            instrumentation/development:
              general:
                not_a_semconv_domain:
                  whatever: true
                http:
                  client:
                    request_captured_headers:
                      - Content-Type
            YAML)])->create(new Context());

        $http = $registry->get(HttpConfig::class);
        $this->assertNotNull($http);
        $this->assertSame(['Content-Type'], $http->config['client']['request_captured_headers']);
    }

    /**
     * `ignoreExtraKeys()` applies only to a node's direct children, so tolerance has to be declared
     * at every depth. A key added to any of these nodes in an upstream minor release must not take
     * the SDK down.
     */
    #[DataProvider('unknownNestedKeyProvider')]
    public function test_unknown_nested_general_key_is_dropped(string $yaml): void
    {
        $registry = self::instrumentationFactory()
            ->process([Yaml::parse($yaml)])
            ->create(new Context());

        $this->assertNotNull($registry->get(HttpConfig::class));
    }

    public static function unknownNestedKeyProvider(): iterable
    {
        // `http` is always declared, so the assertion has a HttpConfig to find; the unknown key
        // moves around it.
        yield 'general.http' => [<<<'YAML'
            instrumentation/development:
              general:
                http:
                  brand_new: true
            YAML];
        yield 'general.http.client' => [<<<'YAML'
            instrumentation/development:
              general:
                http:
                  client:
                    brand_new: true
            YAML];
        yield 'general.http.server' => [<<<'YAML'
            instrumentation/development:
              general:
                http:
                  server:
                    brand_new: true
            YAML];
        yield 'general.http.semconv' => [<<<'YAML'
            instrumentation/development:
              general:
                http:
                  semconv:
                    brand_new: true
            YAML];
        yield 'general.code' => [<<<'YAML'
            instrumentation/development:
              general:
                http: {}
                code:
                  brand_new: true
            YAML];
        yield 'general.code.semconv' => [<<<'YAML'
            instrumentation/development:
              general:
                http: {}
                code:
                  semconv:
                    brand_new: true
            YAML];
        yield 'general.sanitization' => [<<<'YAML'
            instrumentation/development:
              general:
                http: {}
                sanitization:
                  brand_new: true
            YAML];
        yield 'general.sanitization.url' => [<<<'YAML'
            instrumentation/development:
              general:
                http: {}
                sanitization:
                  url:
                    brand_new: true
            YAML];
    }

    /**
     * Tolerating unknown keys must not become tolerating anything: a declared key with the wrong
     * type is still a configuration error.
     */
    public function test_nested_known_key_is_still_validated(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        self::instrumentationFactory()->process([Yaml::parse(<<<'YAML'
            instrumentation/development:
              general:
                http:
                  semconv:
                    version: not_an_integer
            YAML)])->create(new Context());
    }

    /**
     * The schema declares these lists as `items: type: string`, but Symfony's `scalarPrototype()`
     * accepts any scalar, so each needs {@see Validation::ensureString()} to reject the rest.
     */
    #[DataProvider('nonStringListProvider')]
    public function test_string_lists_reject_non_string_items(string $yaml): void
    {
        $this->expectException(InvalidConfigurationException::class);
        self::instrumentationFactory()->process([Yaml::parse($yaml)])->create(new Context());
    }

    public static function nonStringListProvider(): iterable
    {
        yield 'sensitive_query_parameters' => [<<<'YAML'
            instrumentation/development:
              general:
                sanitization:
                  url:
                    sensitive_query_parameters: [123]
            YAML];
        yield 'request_captured_headers' => [<<<'YAML'
            instrumentation/development:
              general:
                http:
                  client:
                    request_captured_headers: [123]
            YAML];
        yield 'response_captured_headers' => [<<<'YAML'
            instrumentation/development:
              general:
                http:
                  server:
                    response_captured_headers: [true]
            YAML];
        yield 'known_methods' => [<<<'YAML'
            instrumentation/development:
              general:
                http:
                  client:
                    known_methods: [4.5]
            YAML];
    }

    /**
     * The counterpart to the above: an environment placeholder resolves to a string, so a variable
     * whose value merely looks numeric must still pass.
     */
    public function test_string_lists_accept_substituted_values(): void
    {
        $factory = new ConfigurationFactory(
            ServiceLoader::load(ComponentProvider::class),
            new InstrumentationConfigurationRegistry(),
            new EnvSourceReader([new ArrayEnvSource([
                'TEST_HEADER_NAME' => 'x-request-id',
                'TEST_NUMERIC_HEADER' => '8080',
            ])]),
        );

        $registry = $factory->process([Yaml::parse(<<<'YAML'
            instrumentation/development:
              general:
                http:
                  client:
                    request_captured_headers:
                      - ${TEST_HEADER_NAME}
                      - ${TEST_NUMERIC_HEADER}
            YAML)])->create(new Context());

        $http = $registry->get(HttpConfig::class);
        $this->assertNotNull($http);
        $this->assertSame(['x-request-id', '8080'], $http->config['client']['request_captured_headers']);
    }

    /**
     * The `Context` passed to {@see Configuration::getInstrumentationConfig()} must reach
     * {@see SkippedComponentPlugin}, which is how
     * a file-configured `log_level` will be honoured once the caller has one.
     */
    public function test_context_logger_receives_skipped_component_warning(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(
            LogLevel::WARNING,
            $this->stringContains('Ignoring "php" entry "not_installed"'),
        );

        $registry = self::instrumentationFactory()->process([Yaml::parse(<<<'YAML'
            instrumentation/development:
              php:
                not_installed: {}
            YAML)])->create((new Context())->withLogger($logger));

        $this->assertNull($registry->get(HttpConfig::class));
    }

    private static function instrumentationFactory(): ConfigurationFactory
    {
        return new ConfigurationFactory(
            ServiceLoader::load(ComponentProvider::class),
            new InstrumentationConfigurationRegistry(),
            new EnvSourceReader([]),
        );
    }

    public static function openTelemetryConfigurationDataProvider(): iterable
    {
        yield 'anchors' => [__DIR__ . '/configurations/anchors.yaml'];
        yield 'php-specific' => [__DIR__ . '/configurations/php-specific.yaml'];

        foreach (['snippets', 'examples'] as $dir) {
            $files = glob(__DIR__ . '/configurations/upstream/' . $dir . '/*.yaml') ?: [];
            Assert::assertNotEmpty($files, sprintf('no upstream %s found to parse', $dir));

            foreach ($files as $file) {
                yield $dir . ': ' . basename($file) => [$file];
            }
        }
    }

    public function test_configurators(): void
    {
        $sdk = Configuration::parseFile(__DIR__ . '/configurations/configurators.yaml')->create()->build();
        $tracer_a = $sdk->getTracerProvider()->getTracer('A.foo');
        $tracer_b = $sdk->getTracerProvider()->getTracer('B.foo');
        $tracer_c = $sdk->getTracerProvider()->getTracer('C.foo');

        $this->assertTrue($tracer_a->isEnabled(), 'enabled by configurator');
        $this->assertFalse($tracer_b->isEnabled(), 'disabled by configurator');
        $this->assertFalse($tracer_c->isEnabled(), 'default disabled');

        $logger_a = $sdk->getLoggerProvider()->getLogger('A.foo');
        $logger_b = $sdk->getLoggerProvider()->getLogger('B.foo');
        $logger_c = $sdk->getLoggerProvider()->getLogger('C.foo');

        $this->assertTrue($logger_a->isEnabled(), 'enabled by configurator');
        $this->assertFalse($logger_b->isEnabled(), 'disabled by configurator');
        $this->assertFalse($logger_c->isEnabled(), 'default disabled');

        $meter_a = $sdk->getMeterProvider()->getMeter('A.foo');
        $meter_b = $sdk->getMeterProvider()->getMeter('B.foo');
        $meter_c = $sdk->getMeterProvider()->getMeter('C.foo');

        $this->assertTrue($meter_a->createCounter('cnt')->isEnabled(), 'enabled by configurator');
        $this->assertFalse($meter_b->createCounter('cnt')->isEnabled(), 'disabled by configurator');
        $this->assertFalse($meter_c->createCounter('cnt')->isEnabled(), 'default disabled');
    }

    public function test_resource(): void
    {
        $expectedKeys = [
            'host.name',
            'host.arch',
            'os.type',
            'os.description',
            'os.name',
            'os.version',
            'process.pid',
            'process.executable.path',
            'process.command',
            'process.args_count',
            'process.owner',
            'service.name',
            'service.namespace',
            'service.version',
            'string_key',
            'int_key',
            'bool_key',
            'double_key',
            'string_array_key',
            'int_array_key',
            'bool_array_key',
            'double_array_key',
        ];

        $removedKeys = [
            'process.command_args',
        ];

        $sdk = Configuration::parseFile(__DIR__ . '/configurations/resource.yaml')->create()->build();
        $resource = $this->getResource($sdk);

        $this->assertStringMatchesFormat('https://opentelemetry.io/schemas/%d.%d.%d', $resource->getSchemaUrl() ?? '');
        $attributes = $resource->getAttributes()->toArray();

        foreach ($expectedKeys as $k) {
            $this->assertArrayHasKey($k, $attributes);
        }

        foreach ($removedKeys as $k) {
            $this->assertArrayNotHasKey($k, $attributes);
        }
    }

    public function test_resource_include_exclude(): void
    {
        $expectedKeys = [
            'process.pid',
            'process.executable.path',
            'process.args_count',
            'process.owner',
            'process.runtime.name',
            'service.name',
            'telemetry.distro.name',
            'telemetry.distro.version',
            'telemetry.sdk.language',
            'telemetry.sdk.name',
            'telemetry.sdk.version',
        ];

        $sdk = Configuration::parseFile(__DIR__ . '/configurations/resource-include-exclude.yaml')->create()->build();
        $resource = $this->getResource($sdk);

        $attributes = $resource->getAttributes()->toArray();

        $this->assertEqualsCanonicalizing($expectedKeys, array_keys($attributes));
    }

    public function test_resource_defaults(): void
    {
        $expectedKeys = [
            'service.name',
            'telemetry.distro.name',
            'telemetry.distro.version',
            'telemetry.sdk.language',
            'telemetry.sdk.name',
            'telemetry.sdk.version',
        ];

        $sdk = Configuration::parseFile(__DIR__ . '/configurations/resource-default.yaml')->create()->build();
        $resource = $this->getResource($sdk);

        $attributes = $resource->getAttributes()->toArray();

        $this->assertEqualsCanonicalizing($expectedKeys, array_keys($attributes));
    }

    #[DoesNotPerformAssertions]
    public function test_minimal(): void
    {
        Configuration::parseFile(__DIR__ . '/configurations/minimal.yaml')->create()->build();
    }

    /**
     * `distribution` is the extension point for vendor-specific settings, and
     * `opentelemetry_php/development` is our entry in it. No upstream snippet or example uses the
     * key, so nothing else covers it.
     */
    public function test_distribution_configures_span_suppression_strategy(): void
    {
        $sdk = Configuration::parseFile(__DIR__ . '/configurations/distribution.yaml')->create()->build();
        $tracerProvider = $sdk->getTracerProvider();

        $strategy = (new \ReflectionClass($tracerProvider))
            ->getProperty('spanSuppressionStrategy')
            ->getValue($tracerProvider);

        $this->assertInstanceOf(SemanticConventionSuppressionStrategy::class, $strategy);
    }

    /**
     * The schema sets `minProperties: 1` on `distribution`, but an empty map is indistinguishable
     * from an absent one once the node default has been applied, so both are accepted and behave
     * the same way.
     */
    #[DataProvider('emptyDistributionProvider')]
    public function test_empty_distribution_falls_back_to_the_default_strategy(string $distribution): void
    {
        $factory = new ConfigurationFactory([], new OpenTelemetrySdk(), new EnvSourceReader([]));
        $sdk = $factory->process([Yaml::parse(sprintf("file_format: \"1.0\"\n%s", $distribution))]);
        $tracerProvider = $sdk->create(new Context())->build()->getTracerProvider();

        $strategy = (new \ReflectionClass($tracerProvider))
            ->getProperty('spanSuppressionStrategy')
            ->getValue($tracerProvider);

        $this->assertInstanceOf(NoopSuppressionStrategy::class, $strategy);
    }

    public static function emptyDistributionProvider(): iterable
    {
        yield 'omitted' => [''];
        yield 'empty map' => ['distribution: {}'];
        yield 'null' => ['distribution:'];
    }

    /**
     * `distribution` is open to any vendor by schema, and the SDK only exposes the data rather than
     * owning it, so another distribution's key must not stop the file parsing — otherwise a
     * configuration file shared between distributions is not portable. The value is an unvalidated
     * object, so a nested one is tolerated too.
     */
    public function test_unknown_distribution_is_ignored(): void
    {
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->atLeastOnce())
            ->method('write')
            ->with(LogLevel::WARNING, $this->matchesRegularExpression('/Ignoring "distribution" entry "some_vendor"/'));
        Logging::setLogWriter($logWriter);

        try {
            $factory = new ConfigurationFactory([], new OpenTelemetrySdk(), new EnvSourceReader([]));
            $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
                file_format: "1.0"
                distribution:
                  some_vendor:
                    profiler:
                      enabled: true
                YAML)]);

            $tracerProvider = $sdk->create(new Context())->build()->getTracerProvider();
            $strategy = (new \ReflectionClass($tracerProvider))
                ->getProperty('spanSuppressionStrategy')
                ->getValue($tracerProvider);

            $this->assertInstanceOf(NoopSuppressionStrategy::class, $strategy);
        } finally {
            Logging::reset();
        }
    }

    /**
     * Resource detectors are the one component the spec marks "warn and skip" rather than "error",
     * so that a portable configuration may name detectors another SDK provides.
     *
     * The warning is emitted while creating the SDK, not while parsing, so that a configured
     * `log_level` applies to it ({@see self::test_unknown_resource_detector_warning_honours_log_level}).
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk.md#supported-sdk-plugin-components
     */
    public function test_unknown_resource_detector_is_ignored(): void
    {
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->atLeastOnce())
            ->method('write')
            ->with(LogLevel::WARNING, $this->matchesRegularExpression('/Ignoring "detectors" entry "some_other_sdk_detector"/'));

        try {
            $factory = new ConfigurationFactory(self::spiComponentProviders(), new OpenTelemetrySdk(), new EnvSourceReader([]));
            $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
                file_format: "1.0"
                resource:
                  detection/development:
                    detectors:
                      - some_other_sdk_detector:
                      - host:
                YAML)]);

            Logging::setLogWriter($logWriter);
            $tracerProvider = $sdk->create(new Context())->build()->getTracerProvider();
            $resource = (new \ReflectionClass($tracerProvider))
                ->getProperty('tracerSharedState')
                ->getValue($tracerProvider);
            $resource = (new \ReflectionClass($resource))->getProperty('resource')->getValue($resource);

            // the surviving detector still ran, so skipping did not discard the rest of the list
            $this->assertInstanceOf(ResourceInfo::class, $resource);
            $this->assertArrayHasKey('host.name', $resource->getAttributes()->toArray());
        } finally {
            Logging::reset();
        }
    }

    /**
     * The point of warning at creation rather than parse time: the message goes through
     * `$context->logger`, so a configured `log_level` filters it without the global level being set.
     */
    public function test_unknown_resource_detector_warning_honours_log_level(): void
    {
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->never())->method('write');

        try {
            $factory = new ConfigurationFactory(self::spiComponentProviders(), new OpenTelemetrySdk(), new EnvSourceReader([]));
            $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
                file_format: "1.0"
                log_level: error
                resource:
                  detection/development:
                    detectors:
                      - some_other_sdk_detector:
                YAML)]);

            Logging::setLogWriter($logWriter);
            $sdk->create(new Context());
        } finally {
            Logging::reset();
        }
    }

    /**
     * Unlike resource detectors, every other component type must fail rather than be skipped.
     */
    public function test_unknown_span_processor_is_an_error(): void
    {
        $factory = new ConfigurationFactory(self::spiComponentProviders(), new OpenTelemetrySdk(), new EnvSourceReader([]));

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/unknown provider "some_unknown_processor"/');
        $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            tracer_provider:
              processors:
                - some_unknown_processor:
            YAML)]);
    }

    /**
     * `gauge` is one of the seven `instrument_type` values in file format 1.0, and was the only one
     * missing from the view selector enum, so a schema-valid file was rejected. No upstream snippet
     * selects on it — `View_kitchen_sink.yaml` uses `histogram`.
     *
     * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/schema/meter_provider.yaml
     */
    #[DataProvider('instrumentTypeSelectorProvider')]
    public function test_view_selects_on_instrument_type(string $instrumentType, string $expected): void
    {
        $factory = new ConfigurationFactory(
            [new MetricReaderPeriodic(), new MetricExporterConsole()],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );
        $sdk = $factory->process([Yaml::parse(sprintf(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            meter_provider:
              readers:
              - periodic:
                  exporter:
                    console:
              views:
              - selector:
                  instrument_type: %s
                stream:
                  name: renamed
            YAML, $instrumentType))]);

        $meterProvider = $sdk->create(new Context())->build()->getMeterProvider();
        $viewRegistry = (new \ReflectionProperty($meterProvider::class, 'viewRegistry'))->getValue($meterProvider);
        $this->assertInstanceOf(ViewRegistryInterface::class, $viewRegistry);
        $scope = new InstrumentationScope('test', null, null, Attributes::create([]));

        foreach (self::instrumentTypeSelectorProvider() as [, $type]) {
            $views = $viewRegistry->find(new Instrument($type, 'original', null, null), $scope);

            if ($type === $expected) {
                $this->assertNotNull($views, sprintf('a view matches a %s instrument', $instrumentType));
                $names = [];
                foreach ($views as $view) {
                    $names[] = $view->name;
                }
                $this->assertSame(['renamed'], $names);
            } else {
                $this->assertNull($views, sprintf('%s does not match %s', $instrumentType, $type));
            }
        }
    }

    /**
     * The seven `instrument_type` values of file format 1.0, paired with the SDK constant each must
     * select.
     */
    public static function instrumentTypeSelectorProvider(): iterable
    {
        yield 'counter' => ['counter', InstrumentType::COUNTER];
        yield 'gauge' => ['gauge', InstrumentType::GAUGE];
        yield 'histogram' => ['histogram', InstrumentType::HISTOGRAM];
        yield 'observable_counter' => ['observable_counter', InstrumentType::ASYNCHRONOUS_COUNTER];
        yield 'observable_gauge' => ['observable_gauge', InstrumentType::ASYNCHRONOUS_GAUGE];
        yield 'observable_up_down_counter' => ['observable_up_down_counter', InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER];
        yield 'up_down_counter' => ['up_down_counter', InstrumentType::UP_DOWN_COUNTER];
    }

    public function test_view_selects_on_unit(): void
    {
        $factory = new ConfigurationFactory(
            [new MetricReaderPeriodic(), new MetricExporterConsole()],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );
        $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            meter_provider:
              readers:
              - periodic:
                  exporter:
                    console:
              views:
              - selector:
                  unit: ms
                stream:
                  name: renamed
            YAML)]);

        $meterProvider = $sdk->create(new Context())->build()->getMeterProvider();
        $viewRegistry = (new \ReflectionProperty($meterProvider::class, 'viewRegistry'))->getValue($meterProvider);
        $this->assertInstanceOf(ViewRegistryInterface::class, $viewRegistry);
        $scope = new InstrumentationScope('test', null, null, Attributes::create([]));

        $views = $viewRegistry->find(new Instrument(InstrumentType::HISTOGRAM, 'original', 'ms', null), $scope);
        $this->assertNotNull($views);
        $names = [];
        foreach ($views as $view) {
            $names[] = $view->name;
        }
        $this->assertSame(['renamed'], $names);

        $this->assertNull($viewRegistry->find(new Instrument(InstrumentType::HISTOGRAM, 'original', 's', null), $scope));
        $this->assertNull($viewRegistry->find(new Instrument(InstrumentType::HISTOGRAM, 'original', null, null), $scope));
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/metrics/sdk.md#stream-configuration
     */
    #[DataProvider('viewAttributeKeysProvider')]
    public function test_view_attribute_keys(string $attributeKeys, ?array $included, ?array $excluded): void
    {
        $factory = new ConfigurationFactory(
            [new MetricReaderPeriodic(), new MetricExporterConsole()],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );
        $sdk = $factory->process([Yaml::parse(<<<YAML
            file_format: "1.0"
            meter_provider:
              readers:
              - periodic:
                  exporter:
                    console:
              views:
              - selector:
                  instrument_name: i
                stream:
                  $attributeKeys
            YAML)]);

        $meterProvider = $sdk->create(new Context())->build()->getMeterProvider();
        $viewRegistry = (new \ReflectionProperty($meterProvider::class, 'viewRegistry'))->getValue($meterProvider);
        $this->assertInstanceOf(ViewRegistryInterface::class, $viewRegistry);

        $views = $viewRegistry->find(new Instrument(InstrumentType::COUNTER, 'i', null, null), new InstrumentationScope('test', null, null, Attributes::create([])));
        $this->assertNotNull($views);
        $projections = iterator_to_array($views, false);
        $this->assertCount(1, $projections);
        $this->assertNull($projections[0]->attributeKeys);
        $this->assertSame($included, $projections[0]->includedAttributeKeys);
        $this->assertSame($excluded, $projections[0]->excludedAttributeKeys);
    }

    public static function viewAttributeKeysProvider(): iterable
    {
        yield 'omitted' => ['name: renamed', null, null];
        yield 'included patterns' => ['attribute_keys: {included: ["http.*", "a?c"]}', ['http.*', 'a?c'], null];
        yield 'excluded patterns' => ['attribute_keys: {excluded: ["http.*"]}', null, ['http.*']];
        yield 'both' => ['attribute_keys: {included: ["http.*"], excluded: ["http.url"]}', ['http.*'], ['http.url']];
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/schema/common.yaml
     */
    #[DataProvider('emptyViewAttributeKeysProvider')]
    public function test_view_attribute_keys_rejects_empty_list(string $attributeKeys): void
    {
        $factory = new ConfigurationFactory(
            [new MetricReaderPeriodic(), new MetricExporterConsole()],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $this->expectException(InvalidConfigurationException::class);
        $factory->process([Yaml::parse(<<<YAML
            file_format: "1.0"
            meter_provider:
              readers:
              - periodic:
                  exporter:
                    console:
              views:
              - selector:
                  instrument_name: i
                stream:
                  $attributeKeys
            YAML)]);
    }

    public static function emptyViewAttributeKeysProvider(): iterable
    {
        yield 'empty included' => ['attribute_keys: {included: []}'];
        yield 'empty excluded' => ['attribute_keys: {excluded: []}'];
    }

    /**
     * A null projection means the reader's default aggregation applies.
     */
    #[DataProvider('viewAggregationProvider')]
    public function test_view_aggregation_resolves_per_instrument(array $aggregation, Instrument $instrument, ?AggregationInterface $expected): void
    {
        $this->assertEquals($expected, $this->projectedAggregation($aggregation, $instrument));
    }

    public static function viewAggregationProvider(): iterable
    {
        $counter = new Instrument(InstrumentType::COUNTER, 'i', null, null);
        $upDownCounter = new Instrument(InstrumentType::UP_DOWN_COUNTER, 'i', null, null);
        $histogram = new Instrument(InstrumentType::HISTOGRAM, 'i', null, null);
        $advised = new Instrument(InstrumentType::HISTOGRAM, 'i', null, null, ['ExplicitBucketBoundaries' => [1, 2, 3]]);
        $asyncCounter = new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, 'i', null, null);
        $gauge = new Instrument(InstrumentType::GAUGE, 'i', null, null);

        yield 'default on counter' => [['default' => null], $counter, null];
        yield 'default on histogram' => [['default' => null], $histogram, null];
        yield 'drop on counter' => [['drop' => null], $counter, new DropAggregation()];
        yield 'drop on histogram' => [['drop' => null], $histogram, new DropAggregation()];
        yield 'sum on counter' => [['sum' => null], $counter, new SumAggregation(true)];
        yield 'sum on histogram' => [['sum' => null], $histogram, new SumAggregation(true)];
        yield 'sum on observable counter' => [['sum' => null], $asyncCounter, new SumAggregation(true)];
        yield 'sum on up down counter' => [['sum' => null], $upDownCounter, new SumAggregation(false)];
        yield 'sum on gauge' => [['sum' => null], $gauge, new SumAggregation(false)];
        yield 'last_value on counter' => [['last_value' => null], $counter, new LastValueAggregation()];
        yield 'last_value on histogram' => [['last_value' => null], $histogram, new LastValueAggregation()];
        yield 'explicit_bucket_histogram on counter' => [
            ['explicit_bucket_histogram' => ['boundaries' => [1, 2]]],
            $counter,
            new ExplicitBucketHistogramAggregation([1, 2]),
        ];
        yield 'explicit_bucket_histogram boundaries beat advisory' => [
            ['explicit_bucket_histogram' => ['boundaries' => [1.5, 2.5]]],
            $advised,
            new ExplicitBucketHistogramAggregation([1.5, 2.5]),
        ];
        yield 'explicit_bucket_histogram empty boundaries is one bucket' => [
            ['explicit_bucket_histogram' => ['boundaries' => []]],
            $advised,
            new ExplicitBucketHistogramAggregation([]),
        ];
        yield 'explicit_bucket_histogram advisory beats SDK default' => [
            ['explicit_bucket_histogram' => null],
            $advised,
            new ExplicitBucketHistogramAggregation([1, 2, 3]),
        ];
        yield 'explicit_bucket_histogram SDK default' => [
            ['explicit_bucket_histogram' => null],
            $histogram,
            new ExplicitBucketHistogramAggregation([0, 5, 10, 25, 50, 75, 100, 250, 500, 1000]),
        ];
        yield 'explicit_bucket_histogram null boundaries is SDK default' => [
            ['explicit_bucket_histogram' => ['boundaries' => null]],
            $histogram,
            new ExplicitBucketHistogramAggregation([0, 5, 10, 25, 50, 75, 100, 250, 500, 1000]),
        ];
        yield 'explicit_bucket_histogram without min and max' => [
            ['explicit_bucket_histogram' => ['record_min_max' => false]],
            $histogram,
            new ExplicitBucketHistogramAggregation([0, 5, 10, 25, 50, 75, 100, 250, 500, 1000], false),
        ];
    }

    public function test_base2_exponential_bucket_histogram_aggregation_warns_and_uses_reader_default(): void
    {
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->once())->method('write')->with(
            LogLevel::WARNING,
            $this->stringContains('base2_exponential_bucket_histogram'),
            $this->anything(),
        );
        Logging::setLogWriter($logWriter);

        try {
            $this->assertNull($this->projectedAggregation(
                ['base2_exponential_bucket_histogram' => ['max_scale' => -10, 'max_size' => 2, 'record_min_max' => false]],
                new Instrument(InstrumentType::HISTOGRAM, 'i', null, null),
            ));
        } finally {
            Logging::reset();
        }
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/metrics/sdk.md#explicit-bucket-histogram-aggregation
     */
    #[DataProvider('invalidViewAggregationProvider')]
    public function test_invalid_view_aggregation_is_rejected(array $aggregation): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->projectedAggregation($aggregation, new Instrument(InstrumentType::HISTOGRAM, 'i', null, null));
    }

    public static function invalidViewAggregationProvider(): iterable
    {
        yield 'non-numeric boundary' => [['explicit_bucket_histogram' => ['boundaries' => ['one']]]];
        yield 'quoted numeric boundary' => [['explicit_bucket_histogram' => ['boundaries' => ['1']]]];
        yield 'unordered boundaries' => [['explicit_bucket_histogram' => ['boundaries' => [10, 1]]]];
        yield 'duplicate boundaries' => [['explicit_bucket_histogram' => ['boundaries' => [1, 1]]]];
        yield 'boundaries map' => [['explicit_bucket_histogram' => ['boundaries' => ['a' => 1, 'b' => 2]]]];
        yield 'max_scale below -10' => [['base2_exponential_bucket_histogram' => ['max_scale' => -11]]];
        yield 'max_scale above 20' => [['base2_exponential_bucket_histogram' => ['max_scale' => 21]]];
        yield 'max_size below 2' => [['base2_exponential_bucket_histogram' => ['max_size' => 1]]];
        yield 'two aggregations' => [['drop' => null, 'sum' => null]];
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/configuration/data-model.md#environment-variable-substitution
     */
    public function test_explicit_bucket_histogram_boundaries_accept_substituted_values(): void
    {
        $this->assertEquals(
            new ExplicitBucketHistogramAggregation([1.5, 10]),
            $this->projectedAggregation(
                ['explicit_bucket_histogram' => ['boundaries' => ['${LOW}', '${HIGH}']]],
                new Instrument(InstrumentType::HISTOGRAM, 'i', null, null),
                new EnvSourceReader([new ArrayEnvSource(['LOW' => '1.5', 'HIGH' => '10'])]),
            ),
        );
    }

    private function projectedAggregation(array $aggregation, Instrument $instrument, EnvSourceReader $envReader = new EnvSourceReader([])): ?AggregationInterface
    {
        $factory = new ConfigurationFactory(self::spiComponentProviders(), new OpenTelemetrySdk(), $envReader);
        $sdk = $factory->process([[
            'file_format' => '1.0',
            'meter_provider' => [
                'readers' => [['periodic' => ['exporter' => ['console' => null]]]],
                'views' => [['selector' => ['instrument_name' => 'i'], 'stream' => ['aggregation' => $aggregation]]],
            ],
        ]]);

        $meterProvider = $sdk->create(new Context())->build()->getMeterProvider();
        $viewRegistry = (new \ReflectionProperty($meterProvider::class, 'viewRegistry'))->getValue($meterProvider);
        $this->assertInstanceOf(ViewRegistryInterface::class, $viewRegistry);

        $views = $viewRegistry->find($instrument, new InstrumentationScope('test', null, null, Attributes::create([])));
        $this->assertNotNull($views);
        $aggregations = [];
        foreach ($views as $view) {
            $aggregations[] = $view->aggregation;
        }
        $this->assertCount(1, $aggregations);

        return $aggregations[0];
    }

    /**
     * `log_level` is named with an OTel severity but the internal logger is PSR-3, and it is global
     * state, so it must not be applied until the SDK is registered globally.
     */
    public function test_log_level_is_applied_on_register_global_not_on_build(): void
    {
        Logging::reset();
        $default = Logging::logLevel();

        try {
            $factory = new ConfigurationFactory([], new OpenTelemetrySdk(), new EnvSourceReader([]));
            $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
                file_format: "1.0"
                log_level: warn
                YAML)]);

            $builder = $sdk->create(new Context());
            $builder->build();
            $this->assertSame($default, Logging::logLevel(), 'parsing and building must not touch global logging');

            $scope = $builder->buildAndRegisterGlobal();
            $this->assertSame(Logging::level(LogLevel::WARNING), Logging::logLevel(), 'warn maps onto PSR-3 warning');
            $scope->detach();
        } finally {
            Logging::reset();
        }
    }

    /**
     * Declarative configuration ignores ambient environment unless explicitly substituted, so an
     * omitted `log_level` must resolve to the schema default rather than letting OTEL_LOG_LEVEL
     * configure a declaratively configured SDK.
     *
     * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/schema/opentelemetry_configuration.yaml#L18
     */
    public function test_omitted_log_level_defaults_to_info_ignoring_environment(): void
    {
        $this->setEnvironmentVariable('OTEL_LOG_LEVEL', 'debug');
        Logging::reset();

        try {
            $factory = new ConfigurationFactory([], new OpenTelemetrySdk(), new EnvSourceReader([]));
            $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
                file_format: "1.0"
                YAML)]);

            $scope = $sdk->create(new Context())->buildAndRegisterGlobal();
            $this->assertSame(Logging::level(LogLevel::INFO), Logging::logLevel());
            $scope->detach();
        } finally {
            Logging::reset();
        }
    }

    /**
     * The internal log level is process-global, so it must not outlive the global registration that
     * asked for it.
     */
    public function test_log_level_is_restored_when_registration_scope_detaches(): void
    {
        Logging::reset();
        $default = Logging::logLevel();

        try {
            $factory = new ConfigurationFactory([], new OpenTelemetrySdk(), new EnvSourceReader([]));
            $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
                file_format: "1.0"
                log_level: debug
                YAML)]);

            $scope = $sdk->create(new Context())->buildAndRegisterGlobal();
            $this->assertSame(Logging::level(LogLevel::DEBUG), Logging::logLevel());

            $scope->detach();
            $this->assertSame($default, Logging::logLevel(), 'detaching must restore the previous level');
        } finally {
            Logging::reset();
        }
    }

    /**
     * Self-observability is opt-in for environment-based configuration
     * (`OTEL_PHP_INTERNAL_METRICS_ENABLED`), so declarative configuration must not silently enable
     * it. No 1.0 schema key covers this, hence the PHP-specific distribution node.
     */
    #[DataProvider('internalMetricsDataProvider')]
    public function test_self_observability_is_opt_in(string $yaml, bool $expected): void
    {
        $factory = new ConfigurationFactory(self::spiComponentProviders(), new OpenTelemetrySdk(), new EnvSourceReader([]));
        $tracerProvider = $factory->process([Yaml::parse($yaml)])->create(new Context())->build()->getTracerProvider();

        $sharedState = (new \ReflectionProperty(TracerProvider::class, 'tracerSharedState'))->getValue($tracerProvider);
        $counter = (new \ReflectionProperty($sharedState::class, 'spanStartedCounter'))->getValue($sharedState);

        $this->assertSame($expected, $counter !== null);
    }

    #[DataProvider('internalMetricsDataProvider')]
    public function test_batch_processor_self_observability_is_opt_in(string $yaml, bool $expected): void
    {
        $yaml .= "\n" . /** @lang yaml */ <<<'YAML'
            tracer_provider:
              processors:
              - batch:
                  exporter:
                    console:
            logger_provider:
              processors:
              - batch:
                  exporter:
                    console:
            YAML;
        $factory = new ConfigurationFactory(self::spiComponentProviders(), new OpenTelemetrySdk(), new EnvSourceReader([]));
        $sdk = $factory->process([Yaml::parse($yaml)])->create(new Context())->build();

        $tracerSharedState = (new \ReflectionProperty(TracerProvider::class, 'tracerSharedState'))->getValue($sdk->getTracerProvider());
        $spanProcessor = $tracerSharedState->getSpanProcessor();
        $loggerSharedState = (new \ReflectionProperty(LoggerProvider::class, 'loggerSharedState'))->getValue($sdk->getLoggerProvider());
        $logProcessor = (new \ReflectionProperty(MultiLogRecordProcessor::class, 'processors'))->getValue($loggerSharedState->getProcessor())[0];

        $this->assertInstanceOf(BatchSpanProcessor::class, $spanProcessor);
        $this->assertInstanceOf(BatchLogRecordProcessor::class, $logProcessor);
        $spanCounter = (new \ReflectionProperty(BatchSpanProcessor::class, 'spanProcessedCounter'))->getValue($spanProcessor);
        $logCounter = (new \ReflectionProperty(BatchLogRecordProcessor::class, 'logProcessedCounter'))->getValue($logProcessor);
        $this->assertSame($expected, $spanCounter !== null && !$spanCounter instanceof NoopCounter);
        $this->assertSame($expected, $logCounter !== null && !$logCounter instanceof NoopCounter);
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/schema-docs.md#batchspanprocessor
     * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/schema-docs.md#batchlogrecordprocessor
     */
    public function test_batch_processor_schedule_delay_defaults_match_sdk(): void
    {
        $yaml = /** @lang yaml */ <<<'YAML'
            file_format: "1.0"
            tracer_provider:
              processors:
              - batch:
                  exporter:
                    console:
            logger_provider:
              processors:
              - batch:
                  schedule_delay:
                  exporter:
                    console:
            YAML;
        $factory = new ConfigurationFactory(self::spiComponentProviders(), new OpenTelemetrySdk(), new EnvSourceReader([]));
        $sdk = $factory->process([Yaml::parse($yaml)])->create(new Context())->build();

        $tracerSharedState = (new \ReflectionProperty(TracerProvider::class, 'tracerSharedState'))->getValue($sdk->getTracerProvider());
        $spanProcessor = $tracerSharedState->getSpanProcessor();
        $loggerSharedState = (new \ReflectionProperty(LoggerProvider::class, 'loggerSharedState'))->getValue($sdk->getLoggerProvider());
        $logProcessor = (new \ReflectionProperty(MultiLogRecordProcessor::class, 'processors'))->getValue($loggerSharedState->getProcessor())[0];

        $this->assertSame(
            BatchSpanProcessor::DEFAULT_SCHEDULE_DELAY * 1_000_000,
            (new \ReflectionProperty(BatchSpanProcessor::class, 'scheduledDelayNanos'))->getValue($spanProcessor),
        );
        $this->assertSame(
            BatchLogRecordProcessor::DEFAULT_SCHEDULE_DELAY * 1_000_000,
            (new \ReflectionProperty(BatchLogRecordProcessor::class, 'scheduledDelayNanos'))->getValue($logProcessor),
        );
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/schema-docs.md#spanlimits
     */
    #[DataProvider('attributeCountLimitProvider')]
    public function test_event_and_link_attribute_count_limits_default_to_128(array $limits): void
    {
        $yaml = /** @lang yaml */ <<<'YAML'
            file_format: "1.0"
            tracer_provider:
              processors:
              - simple:
                  exporter:
                    console:
            YAML;
        $factory = new ConfigurationFactory(self::spiComponentProviders(), new OpenTelemetrySdk(), new EnvSourceReader([]));
        $sdk = $factory->process([Yaml::parse($yaml), $limits])->create(new Context())->build();
        $tracerSharedState = (new \ReflectionProperty(TracerProvider::class, 'tracerSharedState'))->getValue($sdk->getTracerProvider());
        $spanLimits = $tracerSharedState->getSpanLimits();
        $attributes = array_fill_keys(array_map(static fn (int $i): string => "a$i", range(1, 128)), 'v');

        $this->assertCount(1, $spanLimits->getAttributesFactory()->builder($attributes)->build());
        $this->assertCount(128, $spanLimits->getEventAttributesFactory()->builder($attributes)->build());
        $this->assertCount(128, $spanLimits->getLinkAttributesFactory()->builder($attributes)->build());
    }

    public static function attributeCountLimitProvider(): iterable
    {
        yield 'attribute_limits' => [['attribute_limits' => ['attribute_count_limit' => 1]]];
        yield 'tracer_provider.limits' => [['tracer_provider' => ['limits' => ['attribute_count_limit' => 1]]]];
    }

    public static function internalMetricsDataProvider(): iterable
    {
        yield 'omitted' => ['file_format: "1.0"', false];
        yield 'disabled' => [
            /** @lang yaml */
            <<<'YAML'
                file_format: "1.0"
                distribution:
                  opentelemetry_php/development:
                    internal_metrics_enabled: false
                YAML,
            false,
        ];
        yield 'enabled' => [
            /** @lang yaml */
            <<<'YAML'
                file_format: "1.0"
                distribution:
                  opentelemetry_php/development:
                    internal_metrics_enabled: true
                YAML,
            true,
        ];
    }

    /**
     * Carrying several distributions' settings is the point of `distribution` being open, so ours
     * must still take effect alongside a key we know nothing about.
     */
    public function test_known_distribution_is_applied_alongside_another_vendors(): void
    {
        Logging::setLogWriter($this->createStub(LogWriterInterface::class));

        try {
            $factory = new ConfigurationFactory(
                self::spiComponentProviders(),
                new OpenTelemetrySdk(),
                new EnvSourceReader([]),
            );
            $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
                file_format: "1.0"
                distribution:
                  some_vendor:
                    profiler:
                      enabled: true
                  opentelemetry_php/development:
                    span_suppression_strategy/development:
                      semconv:
                YAML)]);

            $tracerProvider = $sdk->create(new Context())->build()->getTracerProvider();
            $strategy = (new \ReflectionClass($tracerProvider))
                ->getProperty('spanSuppressionStrategy')
                ->getValue($tracerProvider);

            $this->assertInstanceOf(SemanticConventionSuppressionStrategy::class, $strategy);
        } finally {
            Logging::reset();
        }
    }

    public function test_duplicate_propagators(): void
    {
        $sdk = Configuration::parseFile(__DIR__ . '/configurations/propagators-duplicate.yaml')->create()->build();
        $propagator = $sdk->getPropagator();
        $propagatorReflection = new \ReflectionClass($propagator);
        $propagatorsProperty = $propagatorReflection->getProperty('propagators');
        $propagators = $propagatorsProperty->getValue($propagator);
        $this->assertIsArray($propagators);
        $this->assertCount(1, $propagators, 'duplicate was removed');
        $this->assertInstanceOf(TraceContextPropagator::class, $propagators[0]);

        $this->assertCount(2, $propagator->fields());
        $this->assertContains('traceparent', $propagator->fields());
        $this->assertContains('tracestate', $propagator->fields());
    }

    /**
     * `composite_list` is folded into `composite` by a `beforeNormalization()` closure on the
     * `propagator` node, and a parent's closures run before its children's, so substitution has to
     * happen in `preNormalize()` for the fold to see a resolved value.
     */
    public function test_propagators_env_substitution(): void
    {
        $this->setEnvironmentVariable('OTEL_PROPAGATORS', 'b3,baggage');
        $propagators = self::propagatorsFrom(__DIR__ . '/configurations/propagators-env-substitution.yaml');

        $this->assertCount(2, $propagators);
        $this->assertInstanceOf(B3Propagator::class, $propagators[0]);
        $this->assertInstanceOf(BaggagePropagator::class, $propagators[1]);
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/sdk-environment-variables.md#general-sdk-configuration
     */
    public function test_propagator_composite_list_none(): void
    {
        $factory = new ConfigurationFactory(self::spiComponentProviders(), new OpenTelemetrySdk(), new EnvSourceReader([]));
        $propagator = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            propagator:
              composite_list: none
            YAML)])->create(new Context())->build()->getPropagator();

        $this->assertInstanceOf(NoopTextMapPropagator::class, $propagator);
    }

    public function test_propagator_composite_list_none_beside_other_propagators_is_ignored(): void
    {
        $factory = new ConfigurationFactory(self::spiComponentProviders(), new OpenTelemetrySdk(), new EnvSourceReader([]));
        $propagator = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            propagator:
              composite_list: none,tracecontext
            YAML)])->create(new Context())->build()->getPropagator();

        $propagators = (new \ReflectionClass($propagator))->getProperty('propagators')->getValue($propagator);
        $this->assertIsArray($propagators);
        $this->assertCount(1, $propagators);
        $this->assertInstanceOf(TraceContextPropagator::class, $propagators[0]);
    }

    public function test_composite_is_not_a_propagator(): void
    {
        $factory = new ConfigurationFactory(self::spiComponentProviders(), new OpenTelemetrySdk(), new EnvSourceReader([]));

        $this->expectException(InvalidConfigurationException::class);
        $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            propagator:
              composite:
                - composite: [tracecontext]
            YAML)]);
    }

    public function test_propagators_env_substitution_falls_back_to_default(): void
    {
        $this->setEnvironmentVariable('OTEL_PROPAGATORS', null);
        $propagators = self::propagatorsFrom(__DIR__ . '/configurations/propagators-env-substitution.yaml');

        $this->assertCount(2, $propagators);
        $this->assertInstanceOf(TraceContextPropagator::class, $propagators[0]);
        $this->assertInstanceOf(BaggagePropagator::class, $propagators[1]);
    }

    private static function propagatorsFrom(string $file): array
    {
        $propagator = Configuration::parseFile($file)->create()->build()->getPropagator();
        $propagators = (new \ReflectionClass($propagator))->getProperty('propagators')->getValue($propagator);
        Assert::assertIsArray($propagators);

        return array_values($propagators);
    }

    public function test_duplicate_response_propagators(): void
    {
        $sdk = Configuration::parseFile(__DIR__ . '/configurations/experimental-response-propagators-duplicate.yaml')->create()->build();
        $responsePropagator = $sdk->getResponsePropagator();
        $responsePropagatorReflection = new \ReflectionClass($responsePropagator);
        $responsePropagatorsProperty = $responsePropagatorReflection->getProperty('responsePropagators');
        $responsePropagators = $responsePropagatorsProperty->getValue($responsePropagator);
        $this->assertIsArray($responsePropagators);
        $this->assertCount(1, $responsePropagators, 'duplicate was removed');
        $this->assertInstanceOf(ResponsePropagatorInterface::class, $responsePropagators[0]);
    }

    public function test_top_level_response_propagator_is_deprecated_but_applied(): void
    {
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->once())
            ->method('write')
            ->with(LogLevel::WARNING, $this->stringContains('"response_propagator/development" is deprecated'), $this->anything());
        Logging::setLogWriter($logWriter);

        try {
            $factory = new ConfigurationFactory(
                self::spiComponentProviders(),
                new OpenTelemetrySdk(),
                new EnvSourceReader([]),
            );
            $responsePropagator = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
                file_format: "1.0"
                response_propagator/development:
                  composite_list: servertiming
                YAML)])->create(new Context())->build()->getResponsePropagator();

            $this->assertInstanceOf(MultiResponsePropagator::class, $responsePropagator);
        } finally {
            Logging::reset();
        }
    }

    public function test_distribution_response_propagator_composite_list_none(): void
    {
        $factory = new ConfigurationFactory(self::spiComponentProviders(), new OpenTelemetrySdk(), new EnvSourceReader([]));
        $responsePropagator = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            distribution:
              opentelemetry_php/development:
                response_propagator/development:
                  composite_list: none
            YAML)])->create(new Context())->build()->getResponsePropagator();

        $this->assertInstanceOf(NoopResponsePropagator::class, $responsePropagator);
    }

    public function test_composite_is_not_a_response_propagator(): void
    {
        $factory = new ConfigurationFactory(self::spiComponentProviders(), new OpenTelemetrySdk(), new EnvSourceReader([]));

        $this->expectException(InvalidConfigurationException::class);
        $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            distribution:
              opentelemetry_php/development:
                response_propagator/development:
                  composite:
                    - composite: [servertiming]
            YAML)]);
    }

    public function test_distribution_response_propagator_takes_precedence_over_top_level(): void
    {
        Logging::setLogWriter($this->createStub(LogWriterInterface::class));

        try {
            $factory = new ConfigurationFactory(
                self::spiComponentProviders(),
                new OpenTelemetrySdk(),
                new EnvSourceReader([]),
            );
            $responsePropagator = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
                file_format: "1.0"
                response_propagator/development:
                  composite_list: servertiming,traceresponse
                distribution:
                  opentelemetry_php/development:
                    response_propagator/development:
                      composite_list: servertiming
                YAML)])->create(new Context())->build()->getResponsePropagator();

            $responsePropagators = (new \ReflectionClass($responsePropagator))
                ->getProperty('responsePropagators')
                ->getValue($responsePropagator);
            $this->assertIsArray($responsePropagators);
            $this->assertCount(1, $responsePropagators);
        } finally {
            Logging::reset();
        }
    }

    #[DataProvider('unsupportedFileFormatProvider')]
    public function test_unsupported_file_format_is_rejected(string $fileFormat, string $expectedMessage): void
    {
        $factory = new ConfigurationFactory([], new OpenTelemetrySdk(), new EnvSourceReader([]));

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches($expectedMessage);

        $factory->process([Yaml::parse(sprintf('file_format: %s', $fileFormat))]);
    }

    public static function unsupportedFileFormatProvider(): iterable
    {
        // a differing MAJOR may reinterpret properties, so it is an error rather than a warning
        yield 'superseded major' => ['"0.4"', '/unsupported version/'];
        // deliberately far ahead of any plausible schema version, so that implementing a future
        // MAJOR does not turn this row into a false failure
        yield 'future major' => ['"999.0"', '/unsupported version/'];
        // the release candidates carry a meta tag, which 1.0 superseded
        yield 'release candidate rc.1' => ['"1.0-rc.1"', '/expected MAJOR\.MINOR/'];
        yield 'release candidate rc.2' => ['"1.0-rc.2"', '/expected MAJOR\.MINOR/'];
        // an unquoted 1.0 is a YAML float, and would stringify to "1"; it must be rejected
        // outright rather than coerced into something that looks like a version
        yield 'unquoted, parsed as a float' => ['1.0', '/must be of type string/'];
    }

    /**
     * MINOR schema versions are additive, so a file declaring one this SDK does not implement is
     * parsed rather than rejected, with a warning that some of it may be ignored.
     *
     * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/VERSIONING.md#file-format
     */
    public function test_newer_minor_file_format_warns_but_is_accepted(): void
    {
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->atLeastOnce())
            ->method('write')
            ->with(LogLevel::WARNING, $this->matchesRegularExpression('/file_format "1.999" is newer/'));
        Logging::setLogWriter($logWriter);

        try {
            $factory = new ConfigurationFactory([], new OpenTelemetrySdk(), new EnvSourceReader([]));
            $sdk = $factory->process([Yaml::parse('file_format: "1.999"')]);

            $tracerProvider = $sdk->create(new Context())->build()->getTracerProvider();

            $this->assertInstanceOf(TracerProvider::class, $tracerProvider, 'parsed, not rejected');
        } finally {
            Logging::reset();
        }
    }

    public function test_resource_attributes_take_precedence_over_default_attributes(): void
    {
        $factory = new ConfigurationFactory(
            [],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
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
            file_format: "1.0"
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
            file_format: "1.0"
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

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/configuration/sdk-environment-variables.md#declarative-configuration
     */
    public function test_resource_ignores_service_name_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'from-env');
        $factory = new ConfigurationFactory(
            [],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            resource:
              detection/development:
                attributes:
                  excluded: [service.name]
            YAML)]);
        $resource = $this->getResource($sdk->create(new Context())->build());

        $this->assertNotSame('from-env', $resource->getAttributes()->get('service.name'));
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/resource/sdk.md#sdk-provided-resource-attributes
     */
    public function test_resource_falls_back_to_unknown_service_when_service_name_is_excluded(): void
    {
        $factory = new ConfigurationFactory(
            [],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            resource:
              detection/development:
                attributes:
                  excluded: [service.name]
            YAML)]);
        $resource = $this->getResource($sdk->create(new Context())->build());

        $this->assertSame('unknown_service:php', $resource->getAttributes()->get('service.name'));
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/schema-docs.md#resource
     */
    public function test_resource_keeps_configured_schema_url(): void
    {
        $factory = new ConfigurationFactory(
            [],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            resource:
              schema_url: https://opentelemetry.io/schemas/1.16.0
            YAML)]);
        $resource = $this->getResource($sdk->create(new Context())->build());

        $this->assertSame('https://opentelemetry.io/schemas/1.16.0', $resource->getSchemaUrl());
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/configuration/data-model.md#environment-variable-substitution
     */
    #[DataProvider('typedResourceAttributeProvider')]
    public function test_resource_attribute_type_is_applied_after_substitution(string $type, string $value, mixed $expected): void
    {
        $factory = new ConfigurationFactory(
            [],
            new OpenTelemetrySdk(),
            new EnvSourceReader([new ArrayEnvSource([
                'STRING_VALUE' => 'value',
                'BOOL_VALUE' => 'true',
                'INT_VALUE' => '1',
                'FLOAT_VALUE' => '1.1',
                'HEX_VALUE' => '0xdeadbeef',
            ])]),
        );

        $sdk = $factory->process([Yaml::parse(sprintf(<<<'YAML'
            file_format: "1.0"
            resource:
              attributes:
                - name: key
                  value: %s
                  type: %s
            YAML, $value, $type))]);
        $resource = $this->getResource($sdk->create(new Context())->build());

        $this->assertSame($expected, $resource->getAttributes()->get('key'));
    }

    public static function typedResourceAttributeProvider(): iterable
    {
        yield 'string' => ['string', '${STRING_VALUE}', 'value'];
        yield 'numeric string' => ['string', '${INT_VALUE}', '1'];
        yield 'bool' => ['bool', '${BOOL_VALUE}', true];
        yield 'int' => ['int', '${INT_VALUE}', 1];
        yield 'hex int' => ['int', '${HEX_VALUE}', 0xdeadbeef];
        yield 'double' => ['double', '${FLOAT_VALUE}', 1.1];
        yield 'int literal as double' => ['double', '1', 1.0];
        yield 'string_array' => ['string_array', '[${STRING_VALUE}, other]', ['value', 'other']];
        yield 'bool_array' => ['bool_array', '[${BOOL_VALUE}, false]', [true, false]];
        yield 'int_array' => ['int_array', '[${INT_VALUE}, 2]', [1, 2]];
        yield 'double_array' => ['double_array', '[${FLOAT_VALUE}, 2]', [1.1, 2.0]];
        yield 'undefined array item' => ['int_array', '[${UNDEFINED}, 2]', [2]];
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/configuration/data-model.md#environment-variable-substitution
     */
    #[DataProvider('mismatchedResourceAttributeProvider')]
    public function test_resource_attribute_value_must_match_type(string $type, string $value): void
    {
        $factory = new ConfigurationFactory(
            [],
            new OpenTelemetrySdk(),
            new EnvSourceReader([new ArrayEnvSource(['STRING_VALUE' => 'value'])]),
        );

        $this->expectException(InvalidConfigurationException::class);
        $factory->process([Yaml::parse(sprintf(<<<'YAML'
            file_format: "1.0"
            resource:
              attributes:
                - name: key
                  value: %s
                  type: %s
            YAML, $value, $type))]);
    }

    public static function mismatchedResourceAttributeProvider(): iterable
    {
        yield 'int as string' => ['string', '1'];
        yield 'string as int' => ['int', '${STRING_VALUE}'];
        yield 'double as int' => ['int', '1.5'];
        yield 'string as bool' => ['bool', 'value'];
        yield 'array as string' => ['string', '[a, b]'];
        yield 'scalar as array' => ['string_array', 'value'];
        yield 'mixed array' => ['int_array', '[1, value]'];
        yield 'empty array' => ['int_array', '[]'];
    }

    public function test_substitution_reads_env_source_providers(): void
    {
        $sdk = Configuration::parseFile(__DIR__ . '/configurations/env-sources.yaml')->create()->build();

        $this->assertSame('from-test', $this->getResource($sdk)->getAttributes()->get('from_provider'));
    }

    public function test_substitution_reads_getenv(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_TEST_GETENV_ONLY', 'from-getenv');
        unset($_SERVER['OTEL_PHP_TEST_GETENV_ONLY']);

        $sdk = Configuration::parseFile(__DIR__ . '/configurations/env-sources.yaml')->create()->build();

        $this->assertSame('from-getenv', $this->getResource($sdk)->getAttributes()->get('from_getenv'));
    }

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
            [$samplerProvider, new SpanProcessorSimple(), new SpanExporterConsole()],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            resource:
              attributes:
              - { name: service.name, value: test-service }
            tracer_provider:
              processors:
              - simple:
                  exporter:
                    console:
              sampler:
                remote_sampler:
            YAML)]);
        $sdk->create(new Context());

        $this->assertSame('test-service', $samplerProvider->serviceName);
    }

    private function getResource(Sdk $sdk): ResourceInfo
    {
        $tracer = $sdk->getTracerProvider()->getTracer('test');

        $tracerReflection = new \ReflectionClass($tracer);
        $sharedStateProperty = $tracerReflection->getProperty('tracerSharedState');
        $sharedState = $sharedStateProperty->getValue($tracer);

        $stateReflection = new \ReflectionClass($sharedState);
        $resourceProperty = $stateReflection->getProperty('resource');
        $resource = $resourceProperty->getValue($sharedState);

        return $resource;
    }

    public function test_empty_tracer_configurator_config(): void
    {
        $file = __DIR__ . '/configurations/empty-tracer-config.yaml';
        $sdk = Configuration::parseFile($file)->create()->build();

        $this->assertTrue($sdk->getTracerProvider()->getTracer('test')->isEnabled());
        $this->assertTrue($sdk->getMeterProvider()->getMeter('test')->createCounter('cnt')->isEnabled());
        $this->assertTrue($sdk->getLoggerProvider()->getLogger('test')->isEnabled());
    }

    #[DataProvider('nullObjectProvider')]
    #[DoesNotPerformAssertions]
    public function test_null_object_takes_defaults(string $yaml): void
    {
        $factory = new ConfigurationFactory(self::spiComponentProviders(), new OpenTelemetrySdk(), new EnvSourceReader([]));
        $config = $factory->process([Yaml::parse("file_format: \"1.0\"\n" . $yaml)]);

        set_error_handler(static function (int $errno, string $errstr): never {
            throw new \ErrorException($errstr, 0, $errno);
        });

        try {
            $config->create(new Context())->build();
        } finally {
            restore_error_handler();
        }
    }

    public static function nullObjectProvider(): iterable
    {
        $tracerProvider = /** @lang yaml */ <<<'YAML'
            tracer_provider:
              processors:
              - simple:
                  exporter:
                    console:

            YAML;

        yield 'tracer_provider.limits' => [$tracerProvider . '  limits:'];
        yield 'resource' => ['resource:'];
        yield 'attribute_limits' => ['attribute_limits:'];
        yield 'propagator' => ['propagator:'];
        yield 'propagator.composite' => ["propagator:\n  composite:"];
        yield 'resource.attributes' => ["resource:\n  attributes:"];
        yield 'meter_provider.views' => [
            /** @lang yaml */
            <<<'YAML'
                meter_provider:
                  readers:
                  - periodic:
                      exporter:
                        console:
                  views:
                YAML,
        ];

        $exporters = [
            'tracer_provider' => ['processors', 'simple'],
            'logger_provider' => ['processors', 'simple'],
            'meter_provider' => ['readers', 'periodic'],
        ];
        foreach ($exporters as $provider => [$list, $component]) {
            foreach (['otlp_http', 'otlp_grpc'] as $exporter) {
                yield "$provider $exporter headers" => [
                    "$provider:\n  $list:\n  - $component:\n      exporter:\n        $exporter:\n          headers:",
                ];
            }
        }
    }

    /**
     * The composable samplers are nested several levels below `sampler`, where a node that is
     * not really validated still lets its snippet parse. These assert the nesting rejects what
     * the schema rejects, so that `Sampler_rule_based_kitchen_sink.yaml` passing is meaningful.
     */
    #[DataProvider('invalidComposableSamplerProvider')]
    public function test_invalid_composable_sampler_is_rejected(string $sampler, string $expectedMessage): void
    {
        $factory = new ConfigurationFactory(
            self::spiComponentProviders(),
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches($expectedMessage);

        $factory->process([Yaml::parse(sprintf(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            tracer_provider:
              processors:
              - simple:
                  exporter:
                    console:
              sampler:
            %s
            YAML, $sampler))]);
    }

    public static function invalidComposableSamplerProvider(): iterable
    {
        // the schema pins ExperimentalComposableSampler to minProperties/maxProperties 1, which
        // componentMap() does not enforce for us
        yield 'two composable samplers' => ['    composite/development:
                  always_on:
                  always_off:', '/exactly one composable sampler/'];
        yield 'no composable sampler' => ['    composite/development: {}', '/exactly one composable sampler/'];
        yield 'unknown composable sampler' => ['    composite/development:
                  not_a_sampler:', '/unknown provider "not_a_sampler"/'];
        // a composable sampler is a distinct schema type: the top-level-only samplers must not
        // be reachable here, nor the composable-only ones reachable at the top level
        yield 'top-level sampler nested as composable' => ['    composite/development:
                  parent_based:
                    root:
                      always_on:', '/unknown provider "parent_based"/'];
        yield 'composable sampler used at top level' => ['    rule_based:
                  rules:
                  - sampler:
                      always_on:', '/unknown provider "rule_based"/'];
        // rules and their match conditions
        yield 'rule without a sampler' => ['    composite/development:
                  rule_based:
                    rules:
                    - span_kinds: [server]', '/"sampler" .* must be configured/'];
        yield 'unknown match condition' => ['    composite/development:
                  rule_based:
                    rules:
                    - not_a_condition: true
                      sampler:
                        always_on:', '/Unrecognized option "not_a_condition"/'];
        yield 'invalid span kind' => ['    composite/development:
                  rule_based:
                    rules:
                    - span_kinds: [not_a_kind]
                      sampler:
                        always_on:', '/"not_a_kind" is not allowed/'];
        yield 'invalid parent' => ['    composite/development:
                  rule_based:
                    rules:
                    - parent: [not_a_parent]
                      sampler:
                        always_on:', '/"not_a_parent" is not allowed/'];
        yield 'attribute_values without values' => ['    composite/development:
                  rule_based:
                    rules:
                    - attribute_values:
                        key: http.route
                      sampler:
                        always_on:', '/"values" .* must be configured/'];
        // ratio bounds, on both the composable and the top-level probability sampler
        yield 'composable ratio above one' => ['    composite/development:
                  rule_based:
                    rules:
                    - sampler:
                        probability:
                          ratio: 1.5', '/value 1\.5 is too big for path "probability\.ratio"/'];
        yield 'probability ratio above one' => ['    probability/development:
                  ratio: 1.5', '/value 1\.5 is too big for path "probability\/development\.ratio"/'];
        yield 'probability ratio below zero' => ['    probability/development:
                  ratio: -0.5', '/value -0\.5 is too small for path "probability\/development\.ratio"/'];
    }

    /**
     * An omitted provider is a noop, but a present one needs at least one processor or reader.
     */
    #[DataProvider('providerWithoutProcessorsProvider')]
    public function test_provider_without_processors_is_rejected(string $yaml, string $expectedMessage): void
    {
        $factory = new ConfigurationFactory(
            self::spiComponentProviders(),
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches($expectedMessage);

        $factory->process([Yaml::parse($yaml)]);
    }

    public static function providerWithoutProcessorsProvider(): iterable
    {
        foreach (['tracer_provider' => 'processors', 'logger_provider' => 'processors', 'meter_provider' => 'readers'] as $provider => $list) {
            $message = sprintf('/path ".*%s".*at least one %s/', $provider, rtrim($list, 's'));
            yield "$provider empty" => [sprintf("file_format: \"1.0\"\n%s: {}", $provider), $message];
            yield "$provider null" => [sprintf("file_format: \"1.0\"\n%s: ~", $provider), $message];
            yield "$provider empty $list" => [sprintf("file_format: \"1.0\"\n%s:\n  %s: []", $provider, $list), $message];
        }
    }

    public function test_omitted_providers_parse(): void
    {
        $factory = new ConfigurationFactory(
            self::spiComponentProviders(),
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $this->expectNotToPerformAssertions();

        $factory->process([Yaml::parse('file_format: "1.0"')])->create(new Context());
    }

    /**
     * The schema has `exclusiveMinimum: 0` on these, so zero is invalid.
     */
    #[DataProvider('zeroExclusiveMinimumProvider')]
    public function test_zero_is_rejected_where_schema_has_exclusive_minimum(string $yaml, string $path): void
    {
        $factory = new ConfigurationFactory(
            self::spiComponentProviders(),
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches(sprintf('/value 0 is too small for path ".*%s"/', preg_quote($path, '/')));

        $factory->process([Yaml::parse($yaml)]);
    }

    public static function zeroExclusiveMinimumProvider(): iterable
    {
        foreach (['max_queue_size', 'max_export_batch_size'] as $key) {
            yield "span batch $key" => [sprintf(/** @lang yaml */<<<'YAML'
                file_format: "1.0"
                tracer_provider:
                  processors:
                  - batch:
                      %s: 0
                      exporter:
                        console:
                YAML, $key), "batch.$key"];
            yield "log batch $key" => [sprintf(/** @lang yaml */<<<'YAML'
                file_format: "1.0"
                logger_provider:
                  processors:
                  - batch:
                      %s: 0
                      exporter:
                        console:
                YAML, $key), "batch.$key"];
        }
        yield 'view aggregation_cardinality_limit' => [/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            meter_provider:
              readers:
              - periodic:
                  exporter:
                    console:
              views:
              - selector:
                  instrument_name: test
                stream:
                  aggregation_cardinality_limit: 0
            YAML, 'stream.aggregation_cardinality_limit'];
        foreach (['default', 'counter', 'gauge', 'histogram', 'observable_counter', 'observable_gauge', 'observable_up_down_counter', 'up_down_counter'] as $key) {
            yield "reader cardinality_limits $key" => [sprintf(/** @lang yaml */<<<'YAML'
                file_format: "1.0"
                meter_provider:
                  readers:
                  - periodic:
                      exporter:
                        console:
                      cardinality_limits:
                        %s: 0
                YAML, $key), "cardinality_limits.$key"];
        }
    }

    public function test_null_cardinality_limits_parse(): void
    {
        $factory = new ConfigurationFactory(
            self::spiComponentProviders(),
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $this->expectNotToPerformAssertions();

        $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            meter_provider:
              readers:
              - periodic:
                  exporter:
                    console:
                  cardinality_limits:
                    default: ~
                    counter: ~
              views:
              - selector:
                  instrument_name: test
                stream:
                  aggregation_cardinality_limit: ~
            YAML)]);
    }

    /**
     * The configured `probability/development` ratio should reach the sampler rather than being
     * parsed and dropped.
     */
    public function test_probability_sampler_honours_ratio(): void
    {
        $factory = new ConfigurationFactory(
            self::spiComponentProviders(),
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<'YAML'
            file_format: "1.0"
            tracer_provider:
              processors:
              - simple:
                  exporter:
                    console:
              sampler:
                probability/development:
                  ratio: 0.001
            YAML)]);

        $sampler = $this->getSampler($sdk->create(new Context())->build());

        $this->assertInstanceOf(CompositeSampler::class, $sampler);
        $this->assertSame('ComposableProbability{ratio=0.001}', $sampler->getDescription());
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/schema-docs.md#traceidratiobasedsampler
     * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/schema-docs.md#parentbasedsampler
     */
    #[DataProvider('samplerDefaultsProvider')]
    public function test_sampler_properties_take_schema_defaults(string $sampler, string $expected): void
    {
        $factory = new ConfigurationFactory(self::spiComponentProviders(), new OpenTelemetrySdk(), new EnvSourceReader([]));
        $sdk = $factory->process([Yaml::parse(/** @lang yaml */<<<YAML
            file_format: "1.0"
            tracer_provider:
              processors:
              - simple:
                  exporter:
                    console:
              sampler:
                $sampler
            YAML)]);

        $this->assertSame($expected, $this->getSampler($sdk->create(new Context())->build())->getDescription());
    }

    public static function samplerDefaultsProvider(): iterable
    {
        yield 'trace_id_ratio_based omitted ratio' => ['trace_id_ratio_based: {}', 'TraceIdRatioBased{1.000000}'];
        yield 'trace_id_ratio_based null ratio' => ['trace_id_ratio_based: {ratio: ~}', 'TraceIdRatioBased{1.000000}'];
        yield 'trace_id_ratio_based null' => ['trace_id_ratio_based:', 'TraceIdRatioBased{1.000000}'];
        yield 'parent_based omitted root' => ['parent_based: {}', 'ParentBased+AlwaysOnSampler'];
        yield 'parent_based null' => ['parent_based:', 'ParentBased+AlwaysOnSampler'];
    }

    /**
     * This directory's test-only providers are registered via SPI, like the SDK's own, so loading
     * that yields both. Needed by tests whose configuration names a provider the SDK does not ship,
     * such as the composable samplers.
     *
     * @return array<class-string, ComponentProvider>
     */
    private static function spiComponentProviders(): array
    {
        return [...ServiceLoader::load(ComponentProvider::class)];
    }

    private function getSampler(Sdk $sdk): SamplerInterface
    {
        $tracer = $sdk->getTracerProvider()->getTracer('test');

        $tracerReflection = new \ReflectionClass($tracer);
        $sharedStateProperty = $tracerReflection->getProperty('tracerSharedState');
        $sharedState = $sharedStateProperty->getValue($tracer);

        $stateReflection = new \ReflectionClass($sharedState);
        $samplerProperty = $stateReflection->getProperty('sampler');

        return $samplerProperty->getValue($sharedState);
    }
}

/**
 * @implements ComponentProvider<TextMapPropagatorInterface>
 */
final class LegacySpiKeyProvider implements ComponentProvider
{
    #[Override]
    public function createPlugin(array $properties, Context $context): TextMapPropagatorInterface
    {
        return NoopTextMapPropagator::getInstance();
    }

    #[Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('legacy_spi_key');
    }
}
