<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\OpenTelemetrySdk;
use OpenTelemetry\Config\SDK\Configuration\ConfigurationFactory;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\PhpIniEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\ServerEnvSource;
use OpenTelemetry\SDK\SdkBuilder;
use WeakMap;

final class Configuration
{
    /**
     * @param ComponentPlugin<SdkBuilder> $sdkPlugin
     */
    private function __construct(
        private readonly ComponentPlugin $sdkPlugin,
    ) {
    }

    public function create(Context $context = new Context()): SdkBuilder
    {
        return $this->sdkPlugin->create($context);
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
        return new self(self::factory($envReader)->parseFile($file, $cacheFile, $debug));
    }

    /**
     * @return ConfigurationFactory<SdkBuilder>
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
            new OpenTelemetrySdk(),
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
