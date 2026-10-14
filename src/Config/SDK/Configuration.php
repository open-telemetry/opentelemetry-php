<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK;

use function is_array;
use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\ConfigProperties;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\Config\SDK\ComponentProvider\InstrumentationConfigurationRegistry;
use OpenTelemetry\Config\SDK\ComponentProvider\OpenTelemetrySdk;
use OpenTelemetry\Config\SDK\Configuration\ConfigurationFactory;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\PhpIniEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\ServerEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Internal\ConfigurationFileLoader;
use OpenTelemetry\SDK\SdkBuilder;
use WeakMap;

final class Configuration
{
    /** @var array|null loaded configurations, null until the file is read */
    private ?array $configs;

    /**
     * @param ComponentPlugin<SdkBuilder> $sdkPlugin
     * @param string|list<string> $file
     */
    private function __construct(
        private readonly ComponentPlugin $sdkPlugin,
        private readonly string|array $file,
        private readonly ?EnvReader $envReader,
        ?array $configs,
    ) {
        $this->configs = $configs;
    }

    public function create(Context $context = new Context()): SdkBuilder
    {
        return $this->sdkPlugin->create($context);
    }

    /**
     * Instrumentation configuration from the `instrumentation/development` node of the same
     * document, or an empty {@see ConfigProperties} if the node is absent.
     *
     * @experimental
     * @return ConfigurationRegistry
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/api.md#get-instrumentation-config
     */
    public function getInstrumentationConfig(Context $context = new Context()): ConfigProperties
    {
        // Processed on demand rather than in parseFile() so that a node we cannot parse costs the
        // caller its instrumentation config and nothing else; see
        // {@see InstrumentationConfigurationRegistry} for why that independence is worth a second
        // pass over the same document.
        $configs = $this->configs ??= ConfigurationFileLoader::load($this->file);

        return self::instrumentationFactory($this->envReader)
            ->process(self::copy($configs))
            ->create($context);
    }

    /**
     * @param string|list<string> $file
     */
    public static function parseFile(
        string|array $file,
        ?string $cacheFile = null,
        bool $debug = true,
        ?EnvReader $envReader = null,
    ): Configuration {
        if ($cacheFile !== null) {
            // The on-disk cache holds the SDK root only, so leave its load to parseFile() and read
            // the file again if and when instrumentation configuration is asked for.
            return new self(self::sdkFactory($envReader)->parseFile($file, $cacheFile, $debug), $file, $envReader, null);
        }

        $configs = ConfigurationFileLoader::load($file);

        return new self(self::sdkFactory($envReader)->process(self::copy($configs)), $file, $envReader, $configs);
    }

    /**
     * YAML aliases materialise as PHP references under ext-yaml (symfony/yaml deep-copies
     * instead), and references survive the by-value copy
     * {@see \Symfony\Component\Config\Definition\Processor::process()} makes. Environment
     * substitution writes back into the array it is given, so without a deep copy the first root
     * processed would mutate what the second sees.
     */
    private static function copy(array $configs): array
    {
        $copy = [];
        foreach ($configs as $key => $value) {
            $copy[$key] = is_array($value) ? self::copy($value) : $value;
        }

        return $copy;
    }

    /**
     * @return ConfigurationFactory<SdkBuilder>
     */
    private static function sdkFactory(?EnvReader $envReader): ConfigurationFactory
    {
        static $factories = new WeakMap();
        $envReader = self::envReader($envReader);

        return $factories[$envReader] ??= new ConfigurationFactory(
            self::loadComponentProviders(),
            new OpenTelemetrySdk(),
            $envReader,
        );
    }

    /**
     * @return ConfigurationFactory<ConfigurationRegistry>
     */
    private static function instrumentationFactory(?EnvReader $envReader): ConfigurationFactory
    {
        static $factories = new WeakMap();
        $envReader = self::envReader($envReader);

        return $factories[$envReader] ??= new ConfigurationFactory(
            self::loadComponentProviders(),
            new InstrumentationConfigurationRegistry(),
            $envReader,
        );
    }

    private static function envReader(?EnvReader $envReader): EnvReader
    {
        static $defaultEnvReader;

        return $envReader ?? $defaultEnvReader ??= new EnvSourceReader([
            new ServerEnvSource(),
            new PhpIniEnvSource(),
        ]);
    }

    private static function loadComponentProviders(): iterable
    {
        yield from ServiceLoader::load(ComponentProvider::class);

        /** @phpstan-ignore-next-line */
        yield from ServiceLoader::load(Configuration\ComponentProvider::class);
    }
}
