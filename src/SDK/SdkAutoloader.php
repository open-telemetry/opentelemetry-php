<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use InvalidArgumentException;
use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ExtensionHookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\NoopHookManager;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\Config\SDK\Configuration as SdkConfiguration;
use OpenTelemetry\Config\SDK\Configuration\Context as ConfigContext;
use OpenTelemetry\Config\SDK\Instrumentation;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Util\ShutdownHandler;
use OpenTelemetry\SDK\Logs\EventLoggerProviderFactory;
use OpenTelemetry\SDK\Logs\LoggerProviderFactory;
use OpenTelemetry\SDK\Metrics\MeterProviderFactory;
use OpenTelemetry\SDK\Propagation\PropagatorFactory;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\ExporterFactory;
use OpenTelemetry\SDK\Trace\SamplerFactory;
use OpenTelemetry\SDK\Trace\SpanProcessorFactory;
use OpenTelemetry\SDK\Trace\TracerProviderBuilder;

/**
 * @psalm-suppress RedundantCast
 */
class SdkAutoloader
{
    public static function autoload(): bool
    {
        if (!self::isEnabled() || self::isExcludedUrl()) {
            return false;
        }
        if (Configuration::has(Variables::OTEL_PHP_SDK_CONFIG_FILE)) {
            Globals::registerInitializer(fn ($configurator) => self::configFileBasedSdkInitializer($configurator));
            self::configureInstrumentation();
        } else {
            Globals::registerInitializer(fn ($configurator) => self::environmentBasedInitializer($configurator));
        }

        return true;
    }

    public static function environmentBasedInitializer(Configurator $configurator): Configurator
    {
        $propagator = (new PropagatorFactory())->create();
        if (Sdk::isDisabled()) {
            //@see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#general-sdk-configuration
            return $configurator->withPropagator($propagator);
        }
        $hookManager = new ExtensionHookManager(); //to should be enabled/disable by env?
        $emitMetrics = Configuration::getBoolean(Variables::OTEL_PHP_INTERNAL_METRICS_ENABLED);

        $resource = ResourceInfoFactory::defaultResource();
        $exporter = (new ExporterFactory())->create();
        $meterProvider = (new MeterProviderFactory())->create($resource);
        $spanProcessor = (new SpanProcessorFactory())->create($exporter, $emitMetrics ? $meterProvider : null);
        $tracerProvider = (new TracerProviderBuilder())
            ->addSpanProcessor($spanProcessor)
            ->setResource($resource)
            ->setSampler((new SamplerFactory())->create())
            ->build();

        $loggerProvider = (new LoggerProviderFactory())->create($emitMetrics ? $meterProvider : null, $resource);
        $eventLoggerProvider = (new EventLoggerProviderFactory())->create($loggerProvider);

        ShutdownHandler::register($tracerProvider->shutdown(...));
        ShutdownHandler::register($meterProvider->shutdown(...));
        ShutdownHandler::register($loggerProvider->shutdown(...));

        return $configurator
            ->withTracerProvider($tracerProvider)
            ->withMeterProvider($meterProvider)
            ->withLoggerProvider($loggerProvider)
            ->withEventLoggerProvider($eventLoggerProvider)
            ->withPropagator($propagator)
            ->withHookManager($hookManager)
        ;
    }

    public static function configFileBasedSdkInitializer(Configurator $configurator): Configurator
    {
        $file = Configuration::getString(Variables::OTEL_PHP_SDK_CONFIG_FILE);
        $config = SdkConfiguration::parseFile($file);
        $sdk = $config
            ->create()
            ->setAutoShutdown(true)
            ->build();

        return $configurator
            ->withTracerProvider($sdk->getTracerProvider())
            ->withMeterProvider($sdk->getMeterProvider())
            ->withLoggerProvider($sdk->getLoggerProvider())
            ->withEventLoggerProvider($sdk->getEventLoggerProvider())
            ->withPropagator($sdk->getPropagator())
            ->withHookManager($sdk->getHookManager())
        ;
    }

    /**
     * @phan-suppress PhanUndeclaredClassMethod
     */
    private static function configureInstrumentation(): void
    {
        if (!Configuration::has(Variables::OTEL_PHP_INSTRUMENTATION_CONFIG_FILE)) {
            return;
        }
        $file = Configuration::getString(Variables::OTEL_PHP_INSTRUMENTATION_CONFIG_FILE);
        $configuration = Instrumentation::parseFile($file)->create();
        $context = new ConfigContext();
        $storage = Context::storage();
        $hookManager = self::getHookManager();
        foreach (ServiceLoader::load(\OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation::class) as $instrumentation) {
            $instrumentation->register($hookManager, $context, $configuration, $storage);
        }
    }

    /**
     * @phan-suppress PhanUndeclaredClassMethod
     */
    private static function getHookManager(): HookManager
    {
        foreach (ServiceLoader::load(HookManager::class) as $hookManager) {
            return $hookManager;
        }

        return new NoopHookManager();
    }

    /**
     * Test whether a request URI is set, and if it matches the excluded urls configuration option
     *
     * @internal
     */
    public static function isIgnoredUrl(): bool
    {
        $ignoreUrls = Configuration::getList(Variables::OTEL_PHP_EXCLUDED_URLS, []);
        if ($ignoreUrls === []) {
            return false;
        }
        $url = $_SERVER['REQUEST_URI'] ?? null;
        if (!$url) {
            return false;
        }
        foreach ($ignoreUrls as $ignore) {
            if (preg_match(sprintf('|%s|', $ignore), (string) $url) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @internal
     */
    public static function isEnabled(): bool
    {
        try {
            $enabled = Configuration::getBoolean(Variables::OTEL_PHP_AUTOLOAD_ENABLED);
        } catch (InvalidArgumentException) {
            //invalid setting, assume false
            return false;
        }

        return $enabled;
    }

    /**
     * Test whether a request URI is set, and if it matches the excluded urls configuration option
     *
     * @internal
     */
    public static function isExcludedUrl(): bool
    {
        $excludedUrls = Configuration::getList(Variables::OTEL_PHP_EXCLUDED_URLS, []);
        if ($excludedUrls === []) {
            return false;
        }
        $url = $_SERVER['REQUEST_URI'] ?? null;
        if (!$url) {
            return false;
        }
        foreach ($excludedUrls as $excludedUrl) {
            if (preg_match(sprintf('|%s|', $excludedUrl), (string) $url) === 1) {
                return true;
            }
        }

        return false;
    }
}
