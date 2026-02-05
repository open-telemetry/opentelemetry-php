<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\Config\SDK\ComponentProvider\InstrumentationConfigurationRegistry;
use OpenTelemetry\Config\SDK\Configuration\ConfigurationFactory;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\PhpIniEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\ServerEnvSource;
use WeakMap;

final class Instrumentation
{
    /**
     * @param ComponentPlugin<ConfigurationRegistry> $plugin
     */
    private function __construct(
        private readonly ComponentPlugin $plugin,
    ) {
    }

    public function create(Context $context = new Context()): ConfigurationRegistry
    {
        $plugin = $this->plugin;

        return $plugin->create($context);
    }

    /**
     * @param string|list<string> $file
     */
    public static function parseFile(
        string|array $file,
        ?string $cacheFile = null,
        bool $debug = true,
        ?EnvReader $envReader = null,
    ): Instrumentation {
        return new self(self::factory($envReader)->parseFile($file, $cacheFile, $debug));
    }

    /**
     * @return ConfigurationFactory<ConfigurationRegistry>
     */
    private static function factory(?EnvReader $envReader): ConfigurationFactory
    {
        static $defaultEnvReader;
        static $factories = new WeakMap();

        $envReader ??= $defaultEnvReader ??= new EnvSourceReader([
            new ServerEnvSource(),
            new PhpIniEnvSource(),
        ]);

        return $factories[$envReader] ??= new ConfigurationFactory(
            self::loadComponentProviders(),
            new InstrumentationConfigurationRegistry(),
            $envReader,
        );
    }

    private static function loadComponentProviders(): iterable
    {
        yield from ServiceLoader::load(ComponentProvider::class);

        /** @phpstan-ignore-next-line */
        yield from ServiceLoader::load(Configuration\ComponentProvider::class);
    }
}
