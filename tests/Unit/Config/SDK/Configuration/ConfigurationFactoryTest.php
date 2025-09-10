<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\Configuration;

use BadMethodCallException;
use ExampleSDK\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\ConfigurationFactory;
use OpenTelemetry\Config\SDK\Configuration\Environment\ArrayEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\PhpIniEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\ServerEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Internal;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Yaml\Yaml;

#[CoversClass(ConfigurationFactory::class)]
final class ConfigurationFactoryTest extends TestCase
{
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
        array_map('unlink', array_filter((array) glob($this->cacheDir . '/*cache*')));
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
                    $node = new ArrayNodeDefinition('env_substitution');
                    /** @phpstan-ignore-next-line */
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

        /** @todo  int_key_with_unquoted_hex_value is being interpreted as string */
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
                #int_key_with_unquoted_hex_value: ${HEX_VALUE}         # Valid reference to HEX_VALUE without quotes
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
                #int_key_with_unquoted_hex_value: 3735928559    # Interpreted as type int, tag URI tag:yaml.org,2002:int
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
                new ComponentProvider\Trace\SpanExporterZipkin(),
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
}
