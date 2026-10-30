<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\ConfigProperties;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\Config\SDK\ComponentProvider\InstrumentationConfigurationRegistry;
use OpenTelemetry\Config\SDK\ComponentProvider\OpenTelemetrySdk;
use OpenTelemetry\Config\SDK\Configuration\ConfigurationFactory;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceProvider;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\GetenvEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\LazyEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\PhpIniEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\ServerEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Internal\ConfigurationFileLoader;
use OpenTelemetry\SDK\SdkBuilder;
use function sprintf;
use WeakMap;

final class Configuration
{
    use LogsMessagesTrait;

    /**
     * @param ComponentPlugin<SdkBuilder> $sdkPlugin
     * @param array|null $configs loaded configurations, null until the file is read
     */
    private function __construct(
        private readonly ComponentPlugin $sdkPlugin,
        private readonly string $file,
        private readonly ?EnvReader $envReader,
        private ?array $configs = null,
    ) {
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
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/api.md#get-instrumentation-config
     */
    public function getInstrumentationConfig(Context $context = new Context()): ConfigProperties
    {
        // Processed on demand so an unparseable node costs only the instrumentation config; see
        // {@see InstrumentationConfigurationRegistry}.
        $this->configs ??= ConfigurationFileLoader::load($this->file);

        return self::instrumentationFactory($this->envReader)
            ->process($this->configs)
            ->create($context);
    }

    /**
     * Instrumentation configuration from the file, without processing the SDK root, so an invalid
     * SDK root costs nothing here.
     *
     * @internal
     */
    public static function parseInstrumentationConfig(
        string $file,
        Context $context = new Context(),
        ?EnvReader $envReader = null,
    ): ConfigProperties {
        return self::instrumentationFactory($envReader)
            ->process(ConfigurationFileLoader::load($file))
            ->create($context);
    }

    public static function parseFile(
        string $file,
        ?string $cacheFile = null,
        bool $debug = true,
        ?EnvReader $envReader = null,
    ): Configuration {
        if ($cacheFile !== null) {
            // The on-disk cache holds the SDK root only, so leave its load to parseFile() and read
            // the file again if and when instrumentation configuration is asked for.
            return new self(self::sdkFactory($envReader)->parseFile($file, $cacheFile, $debug), $file, $envReader);
        }

        $configs = ConfigurationFileLoader::load($file);

        return new self(self::sdkFactory($envReader)->process($configs), $file, $envReader, $configs);
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

    /**
     * Reads the same sources, in the same order, as the SDK resolver that finds `OTEL_CONFIG_FILE`.
     */
    private static function envReader(?EnvReader $envReader): EnvReader
    {
        static $defaultEnvReader;

        return $envReader ?? $defaultEnvReader ??= new EnvSourceReader([
            ...self::envSourceProviders(),
            new ServerEnvSource(),
            new PhpIniEnvSource(),
            new GetenvEnvSource(),
        ]);
    }

    /**
     * @return list<EnvSource>
     */
    private static function envSourceProviders(): array
    {
        $envSources = [];
        /** @var EnvSourceProvider $envSourceProvider */
        foreach (ServiceLoader::load(EnvSourceProvider::class) as $envSourceProvider) {
            $envSources[] = new LazyEnvSource($envSourceProvider->getEnvSource(...));
        }

        return $envSources;
    }

    private static function loadComponentProviders(): iterable
    {
        yield from ServiceLoader::load(ComponentProvider::class);

        // TODO remove the deprecated SPI key before 1.0
        /** @phpstan-ignore-next-line */
        $legacyKey = Configuration\ComponentProvider::class;
        foreach (ServiceLoader::load($legacyKey) as $class => $provider) {
            self::logWarning(sprintf(
                'Component provider "%s" is registered under the deprecated SPI key "%s", register it under "%s"',
                $class,
                $legacyKey,
                ComponentProvider::class,
            ));

            yield $class => $provider;
        }
    }
}
