<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Context as InstrumentationContext;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\NoopHookManager;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\Config\SDK\Configuration as SdkConfiguration;
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
        if (Configuration::has(Variables::OTEL_EXPERIMENTAL_CONFIG_FILE)) {
            $sdk = self::createAndRegisterFileBasedSdk();
            self::configureInstrumentation($sdk);
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
        ;
    }

    public static function createAndRegisterFileBasedSdk(): Sdk
    {
        $file = Configuration::getString(Variables::OTEL_EXPERIMENTAL_CONFIG_FILE);
        $config = SdkConfiguration::parseFile($file);
        $sdk = $config
            ->create()
            ->setAutoShutdown(true)
            ->build();
        $sdk->registerGlobal();

        return $sdk;
    }

    /**
     * @phan-suppress PhanUndeclaredClassMethod
     */
    private static function configureInstrumentation(Sdk $sdk): void
    {
        if (!Configuration::has(Variables::OTEL_PHP_INSTRUMENTATION_CONFIG_FILE)) {
            return;
        }
        $file = Configuration::getString(Variables::OTEL_PHP_INSTRUMENTATION_CONFIG_FILE);
        $configuration = Instrumentation::parseFile($file)->create();
        $storage = Context::storage();
        $hookManager = self::getHookManager();
        $context = new InstrumentationContext($sdk->getTracerProvider(), $sdk->getMeterProvider(), $sdk->getLoggerProvider());
        foreach (ServiceLoader::load(\OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation::class) as $instrumentation) {
            /** @var \OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation $instrumentation */
            $instrumentation->register($hookManager, $context, $configuration, $storage);
        }
    }

    /**
     * @phan-suppress PhanUndeclaredClassMethod
     */
    private static function getHookManager(): HookManager
    {
        /** @var HookManager $hookManager */
        foreach (ServiceLoader::load(HookManager::class) as $hookManager) {
            $scope = $hookManager->enable(Context::getCurrent())->activate();
            ShutdownHandler::register(function () use ($scope) {
                $scope->detach();
            });

            return $hookManager;
        }

        return new NoopHookManager();
    }

    /**
     * @internal
     */
    public static function isEnabled(): bool
    {
        return Configuration::getBoolean(Variables::OTEL_PHP_AUTOLOAD_ENABLED);
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
