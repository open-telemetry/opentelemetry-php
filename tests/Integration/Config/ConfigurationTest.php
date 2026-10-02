<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Config\SDK\ComponentProvider\OpenTelemetrySdk;
use OpenTelemetry\Config\SDK\ComponentProvider\OutputStreamParser;
use OpenTelemetry\Config\SDK\Configuration;
use OpenTelemetry\Config\SDK\Configuration\ConfigurationFactory;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use OpenTelemetry\Config\SDK\Instrumentation;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppressionStrategy;
use OpenTelemetry\SDK\Trace\SpanSuppression\SemanticConventionSuppressionStrategy\SemanticConventionSuppressionStrategy;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\Tests\Integration\Config\ComponentProvider\Detector\ServiceName;
use org\bovigo\vfs\vfsStream;
use Override;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Yaml\Yaml;

#[CoversNothing]
final class ConfigurationTest extends TestCase
{
    #[\Override]
    public function setUp(): void
    {
        // set up mock file system with /var/log directory, for otlp_file exporter.
        $root = vfsStream::setup('/', null, ['var' => ['log' => []]])->url();

        OutputStreamParser::setRoot($root);
    }

    #[\Override]
    public function tearDown(): void
    {
        OutputStreamParser::reset();
    }

    #[DataProvider('openTelemetryConfigurationDataProvider')]
    public function test_open_telemetry_configuration(string $file): void
    {
        $expectedFailure = self::knownParseFailures()[basename($file)] ?? null;

        if ($expectedFailure === null) {
            $this->expectNotToPerformAssertions();
            Configuration::parseFile($file)->create();

            return;
        }

        // asserted rather than skipped, so that fixing the underlying limitation fails here and
        // forces the entry to be removed
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches($expectedFailure);
        Configuration::parseFile($file)->create();
    }

    /**
     * Upstream files that this SDK cannot parse yet. They are still synced verbatim, so the gap is
     * visible and tracked; remove an entry once its blocker is fixed.
     *
     * `otel-sdk-migration-config.yaml` uses `${ENV}` in `propagator.composite_list`, which is
     * split on commas and folded into `composite` before env substitution runs, so the raw
     * placeholder is looked up as a provider name. See the TODO in
     * `src/Config/SDK/ComponentProvider/OpenTelemetrySdk.php`.
     *
     * @return array<string, non-empty-string> file name => expected exception message pattern
     */
    private static function knownParseFailures(): array
    {
        return [
            'otel-sdk-migration-config.yaml' => '/unknown provider "\$\{OTEL_PROPAGATORS/',
        ];
    }

    #[DataProvider('openTelemetryConfigurationDataProvider')]
    public function test_instrumentation_configuration(string $file): void
    {
        $expectedFailure = self::knownInstrumentationParseFailures()[basename($file)] ?? null;

        if ($expectedFailure === null) {
            $this->expectNotToPerformAssertions();
            Instrumentation::parseFile($file)->create();

            return;
        }

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches($expectedFailure);
        Instrumentation::parseFile($file)->create();
    }

    /**
     * As {@see knownParseFailures()}, but for the `instrumentation/development` root, which
     * {@see Instrumentation::parseFile()} parses and {@see Configuration::parseFile()} discards.
     *
     * @return array<string, non-empty-string> file name => expected exception message pattern
     */
    private static function knownInstrumentationParseFailures(): array
    {
        return [
            'ExperimentalGeneralInstrumentation_semconv_stability_opt_in.yaml' => '/unknown provider "stability_opt_in_list"/',
            'ExperimentalInstrumentation_kitchen_sink.yaml' => '/unknown provider "example"/',
            'otel-sdk-migration-config.yaml' => '/unknown provider "stability_opt_in_list"/',
        ];
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
     * Carrying several distributions' settings is the point of `distribution` being open, so ours
     * must still take effect alongside a key we know nothing about.
     */
    public function test_known_distribution_is_applied_alongside_another_vendors(): void
    {
        Logging::setLogWriter($this->createMock(LogWriterInterface::class));

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
            file_format: "1.0"
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
        $this->expectNotToPerformAssertions();
        Configuration::parseFile($file)->create();
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
     * `probability/development` is the one composable-sampler provider that is not a no-op, so
     * the configured ratio should reach the sampler rather than being parsed and dropped.
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

        $this->assertSame('TraceIdRatioBasedSampler{0.001000}', $sampler->getDescription());
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
