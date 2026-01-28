<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use function class_exists;
use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigProperties;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Context as InstrumentationContext;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManagerInterface;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\NoopHookManager;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\API\Logs\LateBindingLoggerProvider;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Metrics\LateBindingMeterProvider;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Trace\LateBindingTracerProvider;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Config\SDK\Configuration as SdkConfiguration;
use OpenTelemetry\Config\SDK\Instrumentation as SdkInstrumentation;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\EnvComponentLoaderRegistry;
use OpenTelemetry\SDK\Common\Configuration\EnvResolver;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\ConfigEnv\EnvConfiguration;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Propagation\LateBindingResponsePropagator;
use OpenTelemetry\SDK\Propagation\LateBindingTextMapPropagator;
use OpenTelemetry\SDK\Trace\AutoRootSpan;
use OpenTelemetry\SDK\Trace\TracerProvider;
use RuntimeException;
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
            if (!class_exists(SdkConfiguration::class)) {
                throw new RuntimeException('File-based configuration requires open-telemetry/sdk-configuration');
            }
            Globals::registerInitializer(fn ($configurator) => self::fileBasedInitializer($configurator));
        } else {
            Globals::registerInitializer(fn ($configurator) => self::environmentBasedInitializer($configurator));
        }
        self::registerInstrumentations();

        if (AutoRootSpan::isEnabled()) {
            $request = AutoRootSpan::createRequest();
            if ($request) {
                AutoRootSpan::create($request);
                AutoRootSpan::registerShutdownHandler();
            }
        }

        return true;
    }

    /**
     * @phan-suppress PhanPossiblyUndeclaredVariable,PhanDeprecatedClass,PhanDeprecatedFunction
     */
    private static function environmentBasedInitializer(Configurator $configurator): Configurator
    {
        $scope = HookManager::disable(Context::getCurrent())->activate();

        try {
            $sdk = EnvConfiguration::loadFromEnv()
                ->setAutoShutdown(true)
                ->build();
        } finally {
            $scope->detach();
        }

        $tracerProvider = $sdk->getTracerProvider();
        $meterProvider = $sdk->getMeterProvider();
        $loggerProvider = $sdk->getLoggerProvider();

        if ($tracerProvider instanceof TracerProvider) {
            $configurator = $configurator->withTracerProvider($tracerProvider);
        }
        if ($meterProvider instanceof MeterProvider) {
            $configurator = $configurator->withMeterProvider($meterProvider);
        }
        if ($loggerProvider instanceof LoggerProvider) {
            $configurator = $configurator->withLoggerProvider($loggerProvider);
        }

        return $configurator
            ->withPropagator($sdk->getPropagator())
            ->withResponsePropagator($sdk->getResponsePropagator())
        ;
    }

    /**
     * @phan-suppress PhanPossiblyUndeclaredVariable,PhanDeprecatedFunction
     */
    private static function fileBasedInitializer(Configurator $configurator): Configurator
    {
        $file = Configuration::getString(Variables::OTEL_EXPERIMENTAL_CONFIG_FILE);
        $config = SdkConfiguration::parseFile($file);

        //disable hook manager during SDK to avoid autoinstrumenting SDK exporters.
        $scope = HookManager::disable(Context::getCurrent())->activate();

        try {
            $sdk = $config
                ->create()
                ->setAutoShutdown(true)
                ->build();
        } finally {
            $scope->detach();
        }

        return $configurator
            ->withTracerProvider($sdk->getTracerProvider())
            ->withMeterProvider($sdk->getMeterProvider())
            ->withLoggerProvider($sdk->getLoggerProvider())
            ->withPropagator($sdk->getPropagator())
            ->withEventLoggerProvider($sdk->getEventLoggerProvider())
            ->withResponsePropagator($sdk->getResponsePropagator())
        ;
    }

    /**
     * Register all {@link Instrumentation} configured through SPI
     * @psalm-suppress ArgumentTypeCoercion
     */
    private static function registerInstrumentations(): void
    {
        $files = Configuration::has(Variables::OTEL_EXPERIMENTAL_CONFIG_FILE)
            ? Configuration::getList(Variables::OTEL_EXPERIMENTAL_CONFIG_FILE)
            : [];
        if (class_exists(SdkInstrumentation::class) && $files) {
            $configuration = SdkInstrumentation::parseFile($files)->create();
        } else {
            $configuration = self::loadConfigPropertiesFromEnv();
        }
        $hookManager = self::getHookManager();
        $tracerProvider = self::createLateBindingTracerProvider();
        $meterProvider = self::createLateBindingMeterProvider();
        $loggerProvider = self::createLateBindingLoggerProvider();
        $propagator = self::createLateBindingTextMapPropagator();
        $responsePropagator = self::createLateBindingResponsePropagator();
        $context = new InstrumentationContext($tracerProvider, $meterProvider, $loggerProvider, $propagator, $responsePropagator);

        foreach (ServiceLoader::load(Instrumentation::class) as $instrumentation) {
            /** @var Instrumentation $instrumentation */
            try {
                $instrumentation->register($hookManager, $configuration, $context);
            } catch (Throwable $t) {
                self::logError(sprintf('Unable to load instrumentation: %s', $instrumentation::class), ['exception' => $t]);
            }
        }
    }

    private static function loadConfigPropertiesFromEnv(): ConfigProperties
    {
        $loaderRegistry = new EnvComponentLoaderRegistry();
        foreach (ServiceLoader::load(EnvComponentLoader::class) as $loader) {
            $loaderRegistry->register($loader);
        }

        $env = new EnvResolver();
        $context = new \OpenTelemetry\API\Configuration\Context();

        $configuration = new ConfigurationRegistry();
        foreach ($loaderRegistry->loadAll(GeneralInstrumentationConfiguration::class, $env, $context) as $instrumentation) {
            $configuration->add($instrumentation);
        }
        foreach ($loaderRegistry->loadAll(InstrumentationConfiguration::class, $env, $context) as $instrumentation) {
            $configuration->add($instrumentation);
        }

        return $configuration;
    }

    private static function createLateBindingTracerProvider(): TracerProviderInterface
    {
        return new LateBindingTracerProvider(static function (): TracerProviderInterface {
            $scope = Context::getRoot()->activate();

            try {
                return Globals::tracerProvider();
            } finally {
                $scope->detach();
            }
        });
    }

    private static function createLateBindingMeterProvider(): MeterProviderInterface
    {
        return new LateBindingMeterProvider(static function (): MeterProviderInterface {
            $scope = Context::getRoot()->activate();

            try {
                return Globals::meterProvider();
            } finally {
                $scope->detach();
            }
        });
    }
    private static function createLateBindingLoggerProvider(): LoggerProviderInterface
    {
        return new LateBindingLoggerProvider(static function (): LoggerProviderInterface {
            $scope = Context::getRoot()->activate();

            try {
                return Globals::loggerProvider();
            } finally {
                $scope->detach();
            }
        });
    }

    private static function createLateBindingTextMapPropagator(): TextMapPropagatorInterface
    {
        return new LateBindingTextMapPropagator(static function (): TextMapPropagatorInterface {
            $scope = Context::getRoot()->activate();

            try {
                return Globals::propagator();
            } finally {
                $scope->detach();
            }
        });
    }

    private static function createLateBindingResponsePropagator(): ResponsePropagatorInterface
    {
        return new LateBindingResponsePropagator(static function (): ResponsePropagatorInterface {
            $scope = Context::getRoot()->activate();

            try {
                return Globals::responsePropagator();
            } finally {
                $scope->detach();
            }
        });
    }

    private static function getHookManager(): HookManagerInterface
    {
        /** @var HookManagerInterface $hookManager */
        foreach (ServiceLoader::load(HookManagerInterface::class) as $hookManager) {
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
