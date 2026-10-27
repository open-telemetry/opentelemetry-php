<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration;

use BadMethodCallException;
use ExampleSDK\ComponentProvider;
use InvalidArgumentException;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\ConfigurationFactory;
use OpenTelemetry\Config\SDK\Configuration\Environment\ArrayEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\PhpIniEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\ServerEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Internal;
use OpenTelemetry\Config\SDK\Configuration\Loader;
use OpenTelemetry\Config\SDK\Configuration\ResourceCollection;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Yaml\Yaml;

#[CoversClass(ConfigurationFactory::class)]
final class ConfigurationFactoryTest extends TestCase
{
    use TestState;

    public string $cacheDir;
    public $properties;

    #[\Override]
    public function setUp(): void
    {
        $this->cacheDir = __DIR__ . '/configurations';
    }

    #[\Override]
    public function tearDown(): void
    {
        array_map(unlink(...), array_filter((array) glob($this->cacheDir . '/*cache*')));
    }

    /**
     * @psalm-suppress MissingTemplateParam
     */
    public function test_env_substitution_spec_examples(): void
    {
        // see example https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/file-configuration.md#environment-variable-substitution
        $factory = new ConfigurationFactory(
            [],
            new class() implements \OpenTelemetry\API\Configuration\Config\ComponentProvider {
                #[\Override]
                public function createPlugin(array $properties, Context $context): mixed
                {
                    throw new BadMethodCallException();
                }

                /**
                 * @psalm-suppress UndefinedInterfaceMethod,PossiblyNullReference
                 */
                #[\Override]
                public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
                {
                    $node = $builder->arrayNode('env_substitution');
                    $node
                        ->children()
                            ->scalarNode('string_key')->end()
                            ->scalarNode('env_string_key')->end()
                            ->scalarNode('other_string_key')->end()
                            ->scalarNode('another_string_key')->end()
                            ->scalarNode('string_key_with_quoted_hex_value')->end()
                            ->scalarNode('yet_another_string_key')->end()
                            ->booleanNode('bool_key')->end()
                            ->integerNode('int_key')->end()
                            ->integerNode('int_key_with_unquoted_hex_value')->end()
                            ->floatNode('float_key')->end()
                            ->scalarNode('combo_string_key')->end()
                            ->scalarNode('string_key_with_default')->end()
                            ->variableNode('undefined_key')->end()
                            ->variableNode('${STRING_VALUE}')->end()
                            ->scalarNode('recursive_key')->end()
                        ->end()
                    ;

                    return $node;
                }
            },
            new EnvSourceReader([
                new ArrayEnvSource([
                    'STRING_VALUE' => 'value',
                    'BOOl_VALUE' => 'true',
                    'INT_VALUE' => '1',
                    'FLOAT_VALUE' => '1.1',
                    'HEX_VALUE' => '0xdeadbeef',
                    'INVALID_MAP_VALUE' => "value\nkey:value",
                    'DO_NOT_REPLACE_ME' => 'Never use this value', // An unused environment variable
                    'REPLACE_ME' => '${DO_NOT_REPLACE_ME}', // A valid replacement text, used verbatim, not replaced with "Never use this value"
                ]),
            ]),
        );

        $parsed = $factory->process([
            Yaml::parse(<<<'YAML'
                string_key: ${STRING_VALUE}                           # Valid reference to STRING_VALUE
                env_string_key: ${env:STRING_VALUE}                   # Valid reference to STRING_VALUE
                other_string_key: "${STRING_VALUE}"                   # Valid reference to STRING_VALUE inside double quotes
                another_string_key: "${BOOl_VALUE}"                   # Valid reference to BOOl_VALUE inside double quotes
                string_key_with_quoted_hex_value: "${HEX_VALUE}"      # Valid reference to HEX_VALUE inside double quotes
                yet_another_string_key: ${INVALID_MAP_VALUE}          # Valid reference to INVALID_MAP_VALUE, but YAML structure from INVALID_MAP_VALUE MUST NOT be injected
                bool_key: ${BOOl_VALUE}                               # Valid reference to BOOl_VALUE
                int_key: ${INT_VALUE}                                 # Valid reference to INT_VALUE
                int_key_with_unquoted_hex_value: ${HEX_VALUE}          # Valid reference to HEX_VALUE without quotes
                float_key: ${FLOAT_VALUE}                             # Valid reference to FLOAT_VALUE
                combo_string_key: foo ${STRING_VALUE} ${FLOAT_VALUE}  # Valid reference to STRING_VALUE and FLOAT_VALUE
                string_key_with_default: ${UNDEFINED_KEY:-fallback}   # UNDEFINED_KEY is not defined but a default value is included
                undefined_key: ${UNDEFINED_KEY}                       # Invalid reference, UNDEFINED_KEY is not defined and is replaced with ""
                ${STRING_VALUE}: value                                # Invalid reference, substitution is not valid in mapping keys and reference is ignored
                recursive_key: ${REPLACE_ME}                          # Valid reference to REPLACE_ME
                YAML),
        ]);

        $this->assertSame(
            Yaml::parse(<<<'YAML'
                string_key: value                              # Interpreted as type string, tag URI tag:yaml.org,2002:str
                env_string_key: value                          # Interpreted as type string, tag URI tag:yaml.org,2002:str
                other_string_key: "value"                      # Interpreted as type string, tag URI tag:yaml.org,2002:str
                another_string_key: "true"                     # Interpreted as type string, tag URI tag:yaml.org,2002:str
                string_key_with_quoted_hex_value: "0xdeadbeef" # Interpreted as type string, tag URI tag:yaml.org,2002:str
                yet_another_string_key: "value\nkey:value"     # Interpreted as type string, tag URI tag:yaml.org,2002:str
                bool_key: true                                 # Interpreted as type bool, tag URI tag:yaml.org,2002:bool
                int_key: 1                                     # Interpreted as type int, tag URI tag:yaml.org,2002:int
                int_key_with_unquoted_hex_value: 3735928559     # Interpreted as type int, tag URI tag:yaml.org,2002:int
                float_key: 1.1                                 # Interpreted as type float, tag URI tag:yaml.org,2002:float
                combo_string_key: foo value 1.1                # Interpreted as type string, tag URI tag:yaml.org,2002:str
                string_key_with_default: fallback              # Interpreted as type string, tag URI tag:yaml.org,2002:str
                # undefined_key removed as null is treated as unset
                undefined_key:                                 # Interpreted as type null, tag URI tag:yaml.org,2002:null
                ${STRING_VALUE}: value                         # Interpreted as type string, tag URI tag:yaml.org,2002:str
                recursive_key: ${DO_NOT_REPLACE_ME}            # Interpreted as type string, tag URI tag:yaml.org,2002:str
                YAML),
            self::getPropertiesFromPlugin($parsed),
        );
    }

    #[BackupGlobals(true)]
    #[CoversNothing]
    public function test_env_substitution_string(): void
    {
        $_SERVER['OTEL_SERVICE_NAME'] = 'example-service';
        $parsed = self::factory()->process([[
            'file_format' => '0.1',
            'resource' => [
                'attributes' => [
                    'service.name' => '${OTEL_SERVICE_NAME}',
                ],
            ],
        ]]);

        $this->assertInstanceOf(ComponentPlugin::class, $parsed);
        $this->assertSame('example-service', self::getPropertiesFromPlugin($parsed)['resource']['attributes']['service.name']);
    }

    #[BackupGlobals(true)]
    #[CoversNothing]
    public function test_env_substitution_with_env_prefix(): void
    {
        $_SERVER['OTEL_SERVICE_NAME'] = 'example-service';
        $parsed = self::factory()->process([[
            'file_format' => '0.1',
            'resource' => [
                'attributes' => [
                    'service.name' => '${env:OTEL_SERVICE_NAME}',
                ],
            ],
        ]]);

        $this->assertInstanceOf(ComponentPlugin::class, $parsed);
        $this->assertSame('example-service', self::getPropertiesFromPlugin($parsed)['resource']['attributes']['service.name']);
    }

    #[BackupGlobals(true)]
    public function test_env_substitution_non_string(): void
    {
        $_SERVER['OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT'] = '2048';
        $parsed = self::factory()->process([[
            'file_format' => '0.1',
            'attribute_limits' => [
                'attribute_value_length_limit' => '${OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT}',
            ],
        ]]);

        $this->assertInstanceOf(ComponentPlugin::class, $parsed);
        $this->assertSame(2048, self::getPropertiesFromPlugin($parsed)['attribute_limits']['attribute_value_length_limit']);
    }

    #[BackupGlobals(true)]
    #[TestWith(['0x'])]
    #[TestWith(['0xZZ'])]
    public function test_env_substitution_rejects_malformed_hex_integer(string $value): void
    {
        $_SERVER['OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT'] = $value;

        $this->expectException(InvalidConfigurationException::class);

        self::factory()->process([[
            'file_format' => '0.1',
            'attribute_limits' => [
                'attribute_value_length_limit' => '${OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT}',
            ],
        ]]);
    }

    /**
     * It MUST NOT be possible to inject environment variable by environment variables.
     * For example, see references to DO_NOT_REPLACE_ME environment variable
     */
    #[BackupGlobals(true)]
    #[CoversNothing]
    public function test_env_substitution_recursive_does_not_inject_environment_variables(): void
    {
        $_SERVER['DO_NOT_REPLACE_ME'] = 'Never use this value';
        $_SERVER['REPLACE_ME'] = '${DO_NOT_REPLACE_ME}';
        $parsed = self::factory()->process([[
            'file_format' => '0.1',
            'resource' => [
                'attributes' => [
                    'service.name' => '${REPLACE_ME}',
                ],
            ],
        ]]);

        $this->assertInstanceOf(ComponentPlugin::class, $parsed);
        $this->assertSame('${DO_NOT_REPLACE_ME}', self::getPropertiesFromPlugin($parsed)['resource']['attributes']['service.name']);
    }

    /**
     * The same invariant as above, but reached through a YAML alias. ext-yaml turns a direct alias
     * into a PHP reference, so substitution's write-back used to hand the alias partner
     * already-substituted text and substitute it a second time — resolving an escaped `$${FOO}` to
     * the value of `FOO`, and expanding a value that itself looks like a placeholder.
     *
     * Needs a real file, and ext-yaml specifically: symfony/yaml deep-copies aliases and so never
     * produced the reference.
     */
    #[BackupGlobals(true)]
    #[RequiresPhpExtension('yaml')]
    #[CoversNothing]
    public function test_env_substitution_through_yaml_alias_does_not_inject_environment_variables(): void
    {
        $_SERVER['DO_NOT_REPLACE_ME'] = 'Never use this value';
        $_SERVER['REPLACE_ME'] = '${DO_NOT_REPLACE_ME}';

        $file = $this->cacheDir . '/alias-substitution.yaml';
        file_put_contents($file, <<<'YAML'
            file_format: "0.1"
            resource:
              attributes: &shared
                - name: escaped
                  value: $${DO_NOT_REPLACE_ME}
                - name: indirect
                  value: ${REPLACE_ME}
              attributes_list: *shared
            YAML);

        try {
            $loader = new Internal\ConfigurationLoader(null);
            (new Loader\YamlExtensionFileLoader($loader, new FileLocator($this->cacheDir)))->load($file);
            $configs = $loader->getConfigurations();

            // The alias partner must hold the same literals, not twice-substituted values.
            $this->assertSame(
                $configs[0]['resource']['attributes'],
                $configs[0]['resource']['attributes_list'],
            );
            $this->assertSame('$${DO_NOT_REPLACE_ME}', $configs[0]['resource']['attributes'][0]['value']);

            // Mutating one side must not reach the other.
            $configs[0]['resource']['attributes'][0]['value'] = 'probe';
            $this->assertSame('$${DO_NOT_REPLACE_ME}', $configs[0]['resource']['attributes_list'][0]['value']);
        } finally {
            @unlink($file);
        }
    }

    /**
     * End-to-end form of the above, driving the ext-yaml loader explicitly: `parseFile()` resolves
     * loaders with symfony/yaml first, so in a checkout that has it the extension loader — and this
     * bug — is never reached. An ext-yaml-only install is a supported deployment (both are merely
     * suggested), which is the case this covers.
     */
    #[BackupGlobals(true)]
    #[RequiresPhpExtension('yaml')]
    #[CoversNothing]
    public function test_escaped_substitution_under_yaml_alias_is_not_resolved(): void
    {
        $_SERVER['DO_NOT_REPLACE_ME'] = 'Never use this value';

        $file = $this->cacheDir . '/alias-escape.yaml';
        file_put_contents($file, <<<'YAML'
            file_format: "0.1"
            resource:
              attributes:
                - &shared
                  name: escaped
                  value: $${DO_NOT_REPLACE_ME}
                - *shared
            YAML);

        try {
            $loader = new Internal\ConfigurationLoader(null);
            (new Loader\YamlExtensionFileLoader($loader, new FileLocator($this->cacheDir)))->load($file);
            $parsed = self::factory()->process($loader->getConfigurations());

            $attributes = self::getPropertiesFromPlugin($parsed)['resource']['attributes'];
            $this->assertSame('${DO_NOT_REPLACE_ME}', $attributes[0]['value']);
            $this->assertSame('${DO_NOT_REPLACE_ME}', $attributes[1]['value']);
        } finally {
            @unlink($file);
        }
    }

    /**
     * A recursive alias resolves to a genuinely self-referential array under ext-yaml, which has no
     * finite by-value copy. It must be rejected with an exception the caller can catch, not left to
     * exhaust memory — autoload degrades to a noop configuration on `Throwable`, and a memory
     * fatal would take the process with it instead. symfony/yaml rejects these at parse time.
     */
    #[RequiresPhpExtension('yaml')]
    #[CoversNothing]
    public function test_recursive_yaml_alias_is_rejected(): void
    {
        $file = $this->cacheDir . '/recursive-alias.yaml';
        file_put_contents($file, <<<'YAML'
            file_format: "0.1"
            resource: &recursive
              attributes:
                - *recursive
            YAML);

        try {
            $loader = new Internal\ConfigurationLoader(null);

            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessageMatches('/recursive YAML alias/');

            (new Loader\YamlExtensionFileLoader($loader, new FileLocator($this->cacheDir)))->load($file);
        } finally {
            @unlink($file);
        }
    }

    /**
     * ext-yaml returns the first document of a stream and drops the rest. symfony/yaml rejects
     * the file, so ext-yaml must too.
     */
    #[RequiresPhpExtension('yaml')]
    #[CoversNothing]
    #[TestWith(["file_format: \"1.0\"\n---\nfile_format: \"1.0\"\n"], 'two documents')]
    #[TestWith(["file_format: \"1.0\"\n---\n"], 'trailing separator')]
    public function test_multiple_yaml_documents_are_rejected(string $yaml): void
    {
        $file = $this->cacheDir . '/multiple-documents.yaml';
        file_put_contents($file, $yaml);

        try {
            $loader = new Internal\ConfigurationLoader(null);

            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessageMatches('/more than one YAML document/');

            (new Loader\YamlExtensionFileLoader($loader, new FileLocator($this->cacheDir)))->load($file);
        } finally {
            @unlink($file);
        }
    }

    #[RequiresPhpExtension('yaml')]
    #[CoversNothing]
    #[TestWith(["---\nfile_format: \"1.0\"\n"], 'leading separator')]
    #[TestWith(["file_format: \"1.0\"\n...\n"], 'document end marker')]
    public function test_single_yaml_document_with_markers_is_accepted(string $yaml): void
    {
        $file = $this->cacheDir . '/single-document.yaml';
        file_put_contents($file, $yaml);

        try {
            $loader = new Internal\ConfigurationLoader(null);
            (new Loader\YamlExtensionFileLoader($loader, new FileLocator($this->cacheDir)))->load($file);

            $this->assertSame([['file_format' => '1.0']], $loader->getConfigurations());
        } finally {
            @unlink($file);
        }
    }

    /**
     * If a property has a default value defined (i.e. is _not_ required) and is
     * missing or present but null, Create MUST ensure the SDK component is configured
     * with the default value.
     */
    #[BackupGlobals(true)]
    public function test_env_substitution_missing_value(): void
    {
        unset($_SERVER['OTEL_ATTRIBUTE_COUNT_LIMIT']);
        $parsed = self::factory()->process([[
            'file_format' => '0.1',
            'attribute_limits' => [
                'attribute_count_limit' => '${OTEL_ATTRIBUTE_COUNT_LIMIT}',
            ],
        ]]);
        $this->assertInstanceOf(ComponentPlugin::class, $parsed);
        $this->assertNull(self::getPropertiesFromPlugin($parsed)['attribute_limits']['attribute_value_length_limit']);
        $this->assertSame(128, self::getPropertiesFromPlugin($parsed)['attribute_limits']['attribute_count_limit']);
    }

    #[CoversNothing]
    public function test_env_substitution_enum_node(): void
    {
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_PROTOCOL', 'http/protobuf');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_COMPRESSION', 'gzip');
        $parsed = self::factory()->process([[
            'file_format' => '0.1',
            'tracer_provider' => [
                'processors' => [
                    ['batch' => ['exporter' => ['otlp' => [
                        'protocol' => '${OTEL_EXPORTER_OTLP_PROTOCOL}',
                        'endpoint' => 'http://localhost:4318',
                        'compression' => '${OTEL_EXPORTER_OTLP_COMPRESSION}',
                    ]]]],
                ],
            ],
        ]]);

        $exporter = self::getPropertiesFromPlugin(
            self::getPropertiesFromPlugin($parsed)['tracer_provider']['processors'][0]
        )['exporter'];
        $this->assertSame('gzip', self::getPropertiesFromPlugin($exporter)['compression']);
    }

    /**
     * Prototype subtrees were never reached while substitution was attached to node definitions,
     * because `getChildNodeDefinitions()` does not include the prototype.
     */
    #[CoversNothing]
    public function test_env_substitution_in_array_prototype(): void
    {
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_HEADER_VALUE', 'secret');
        $parsed = self::factory()->process([[
            'file_format' => '0.1',
            'tracer_provider' => [
                'processors' => [
                    ['batch' => ['exporter' => ['otlp' => [
                        'protocol' => 'http/protobuf',
                        'endpoint' => 'http://localhost:4318',
                        'headers' => [
                            'authorization' => '${OTEL_EXPORTER_OTLP_HEADER_VALUE}',
                        ],
                    ]]]],
                ],
            ],
        ]]);

        $exporter = self::getPropertiesFromPlugin(
            self::getPropertiesFromPlugin($parsed)['tracer_provider']['processors'][0]
        )['exporter'];
        $this->assertSame('secret', self::getPropertiesFromPlugin($exporter)['headers']['authorization']);
    }

    /**
     * @psalm-suppress UndefinedThisPropertyFetch,PossiblyNullFunctionCall
     */
    private function getPropertiesFromPlugin(ComponentPlugin $plugin): array
    {
        assert($plugin instanceof Internal\ComponentPlugin);

        return (fn () => $this->properties)->bindTo($plugin, Internal\ComponentPlugin::class)();
    }

    public static function openTelemetryConfigurationDataProvider(): iterable
    {
        yield 'kitchen-sink' => [__DIR__ . '/configurations/kitchen-sink.yaml'];
        yield 'anchors' => [__DIR__ . '/configurations/anchors.yaml'];
    }

    #[DataProvider('openTelemetryConfigurationDataProvider')]
    public function test_open_telemetry_configuration(string $file): void
    {
        $this->expectNotToPerformAssertions();
        self::factory()->parseFile($file);
    }

    private function factory(): ConfigurationFactory
    {
        return new ConfigurationFactory(
            [
                new ComponentProvider\Propagator\ResponsePropagatorComposite(),
                new ComponentProvider\Propagator\ResponsePropagatorServerTiming(),
                new ComponentProvider\Propagator\ResponsePropagatorTraceResponse(),
                new ComponentProvider\Propagator\TextMapPropagatorB3(),
                new ComponentProvider\Propagator\TextMapPropagatorB3Multi(),
                new ComponentProvider\Propagator\TextMapPropagatorBaggage(),
                new ComponentProvider\Propagator\TextMapPropagatorComposite(),
                new ComponentProvider\Propagator\TextMapPropagatorJaeger(),
                new ComponentProvider\Propagator\TextMapPropagatorOTTrace(),
                new ComponentProvider\Propagator\TextMapPropagatorTraceContext(),
                new ComponentProvider\Propagator\TextMapPropagatorXRay(),

                new ComponentProvider\Trace\SamplerAlwaysOff(),
                new ComponentProvider\Trace\SamplerAlwaysOn(),
                new ComponentProvider\Trace\SamplerParentBased(),
                new ComponentProvider\Trace\SamplerTraceIdRatioBased(),
                new ComponentProvider\Trace\SpanExporterConsole(),
                new ComponentProvider\Trace\SpanExporterOtlp(),
                new ComponentProvider\Trace\SpanProcessorBatch(),
                new ComponentProvider\Trace\SpanProcessorSimple(),

                new ComponentProvider\Metrics\AggregationResolverDefault(),
                new ComponentProvider\Metrics\AggregationResolverDrop(),
                new ComponentProvider\Metrics\AggregationResolverExplicitBucketHistogram(),
                new ComponentProvider\Metrics\AggregationResolverLastValue(),
                new ComponentProvider\Metrics\AggregationResolverSum(),
                new ComponentProvider\Metrics\MetricExporterConsole(),
                new ComponentProvider\Metrics\MetricExporterOtlp(),
                new ComponentProvider\Metrics\MetricExporterPrometheus(),
                new ComponentProvider\Metrics\MetricReaderPeriodic(),
                new ComponentProvider\Metrics\MetricReaderPull(),

                new ComponentProvider\Logs\LogRecordExporterConsole(),
                new ComponentProvider\Logs\LogRecordExporterOtlp(),
                new ComponentProvider\Logs\LogRecordProcessorBatch(),
                new ComponentProvider\Logs\LogRecordProcessorSimple(),
            ],
            new ComponentProvider\OpenTelemetryConfiguration(),
            new EnvSourceReader([
                new ServerEnvSource(),
                new PhpIniEnvSource(),
            ]),
        );
    }

    public function test_cache_configuration(): void
    {
        $file = $this->cacheDir . '/kitchen-sink.yaml';
        $cacheFile = $this->cacheDir . '/kitchen-sink.cache';
        $this->assertFalse(file_exists($cacheFile), 'cache does not initially exist');
        $plugin = self::factory()->parseFile($file, $cacheFile);
        $this->assertTrue(file_exists($cacheFile));
        $fromCache = self::factory()->parseFile($file, $cacheFile);
        $this->assertEquals($fromCache, $plugin);
    }

    /**
     * The cache holds substituted values, which may be secrets.
     */
    public function test_cache_is_readable_by_owner_only(): void
    {
        $cacheFile = $this->cacheDir . '/kitchen-sink.cache';
        $umask = umask(0o022);

        try {
            self::factory()->parseFile($this->cacheDir . '/kitchen-sink.yaml', $cacheFile);
        } finally {
            umask($umask);
        }

        clearstatcache();
        $this->assertSame(0o600, self::mode($cacheFile));
        $this->assertSame(0o600, self::mode($cacheFile . '.meta'));
        $this->assertSame(0o600, self::mode($cacheFile . '.meta.json'));
    }

    public function test_unwritable_cache_is_ignored(): void
    {
        $file = $this->cacheDir . '/kitchen-sink.yaml';
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->once())
            ->method('write')
            ->with(LogLevel::WARNING, $this->stringContains('Failed to write configuration cache'), $this->anything());
        Logging::setLogWriter($logWriter);

        try {
            $this->assertEquals(
                self::factory()->parseFile($file),
                self::factory()->parseFile($file, $file . '/not-a-directory/kitchen-sink.cache'),
            );
        } finally {
            Logging::reset();
        }
    }

    public function test_truncated_cache_is_a_miss(): void
    {
        $file = $this->cacheDir . '/kitchen-sink.yaml';
        $cacheFile = $this->cacheDir . '/kitchen-sink.cache';
        file_put_contents($cacheFile, '<?php return ');

        $this->assertEquals(self::factory()->parseFile($file), self::factory()->parseFile($file, $cacheFile));
        $this->assertNotSame('<?php return ', file_get_contents($cacheFile), 'cache was rewritten');
    }

    public function test_process_accepts_any_resource_collection(): void
    {
        $resources = new class() implements ResourceCollection {
            public array $classes = [];

            #[\Override]
            public function addClassResource(object|string $class): void
            {
                $this->classes[] = is_object($class) ? $class::class : $class;
            }

            #[\Override]
            public function addResource(ResourceInterface $resource): void
            {
            }
        };

        self::factory()->process([Yaml::parseFile($this->cacheDir . '/kitchen-sink.yaml')], $resources);

        $this->assertContains(ComponentProvider\OpenTelemetryConfiguration::class, $resources->classes);
    }

    private static function mode(string $file): int
    {
        $perms = fileperms($file);
        self::assertIsInt($perms);

        return $perms & 0o777;
    }
}
