<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK;

use Nevay\OTelSDK\Configuration\ComponentPlugin;
use Nevay\OTelSDK\Configuration\ComponentProvider;
use Nevay\OTelSDK\Configuration\ConfigurationFactory;
use Nevay\OTelSDK\Configuration\Context;
use Nevay\OTelSDK\Configuration\Environment\EnvSourceReader;
use Nevay\OTelSDK\Configuration\Environment\PhpIniEnvSource;
use Nevay\OTelSDK\Configuration\Environment\ServerEnvSource;
use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Config\SDK\ComponentProvider\OpenTelemetrySdk;
use OpenTelemetry\SDK\SdkBuilder;

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

    public static function parseFile(
        string|array $file,
        ?string $cacheFile = null,
        bool $debug = true,
    ): Configuration {
        return new self(self::factory()->parseFile($file, $cacheFile, $debug));
    }

    /**
     * @return ConfigurationFactory<SdkBuilder>
     */
    private static function factory(): ConfigurationFactory
    {
        static $factory;

        return $factory ??= new ConfigurationFactory(
            ServiceLoader::load(ComponentProvider::class),
            new OpenTelemetrySdk(),
            new EnvSourceReader([
                new ServerEnvSource(),
                new PhpIniEnvSource(),
            ]),
        );
    }
}
