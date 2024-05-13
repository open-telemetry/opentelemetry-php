<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\NoopHookManager;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\Config\SDK\Configuration as SdkConfiguration;
use OpenTelemetry\Config\SDK\Instrumentation;
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
use Throwable;

/**
 * @psalm-suppress RedundantCast
 */
class SdkAutoloader
{
    use LogsMessagesTrait;

    public static function autoload(): bool
    {
        if (!self::isEnabled() || self::isExcludedUrl()) {
            return false;
        }
        if (Configuration::has(Variables::OTEL_EXPERIMENTAL_CONFIG_FILE)) {
            Globals::registerInitializer(fn ($configurator) => self::fileBasedInitializer($configurator));
        } else {
            Globals::registerInitializer(fn ($configurator) => self::environmentBasedInitializer($configurator));
        }
        self::registerInstrumentations();

        return true;
    }

    private static function environmentBasedInitializer(Configurator $configurator): Configurator
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

    private static function fileBasedInitializer(Configurator $configurator): Configurator
    {
        $file = Configuration::getString(Variables::OTEL_EXPERIMENTAL_CONFIG_FILE);
        $config = SdkConfiguration::parseFile($file);

        $sdk = $config
            ->create()
            ->setAutoShutdown(true)
            ->build();

        return $configurator
            ->withTracerProvider($sdk->getTracerProvider())
            ->withMeterProvider($sdk->getMeterProvider())
            ->withLoggerProvider($sdk->getLoggerProvider())
            ->withPropagator($sdk->getPropagator())
            ->withEventLoggerProvider($sdk->getEventLoggerProvider())
        ;
    }

    /**
     * Register any auto-instrumentation configured through SPI
     *
     * @phan-suppress PhanUndeclaredClassMethod
     */
    private static function registerInstrumentations(): void
    {
        $file = Configuration::has(Variables::OTEL_PHP_INSTRUMENTATION_CONFIG_FILE)
            ? Configuration::getString(Variables::OTEL_PHP_INSTRUMENTATION_CONFIG_FILE)
            : [];
        $configuration = Instrumentation::parseFile($file)->create();
        $hookManager = self::getHookManager();
        foreach (ServiceLoader::load(\OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation::class) as $instrumentation) {
            /** @var \OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation $instrumentation */
            try {
                $instrumentation->register($hookManager, $configuration);
            } catch (Throwable $t) {
                self::logError(sprintf('Unable to load instrumentation: %s', $instrumentation::class), ['exception' => $t]);
            }

        }
    }

    /**
     * @phan-suppress PhanUndeclaredClassMethod
     */
    private static function getHookManager(): HookManager
    {
        /** @var HookManager $hookManager */
        foreach (ServiceLoader::load(HookManager::class) as $hookManager) {
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
