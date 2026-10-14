<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use function class_exists;
use Error;
use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigProperties;
use OpenTelemetry\API\Configuration\Noop\NoopConfigProperties;
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
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\EnvComponentLoaderRegistry;
use OpenTelemetry\SDK\Common\Configuration\EnvResolver;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Distribution\DistributionConfiguration;
use OpenTelemetry\SDK\Common\Distribution\DistributionProperties;
use OpenTelemetry\SDK\Common\Distribution\DistributionRegistry;
use OpenTelemetry\SDK\Common\Distribution\SdkDistribution;
use OpenTelemetry\SDK\Common\Util\ShutdownHandler;
use OpenTelemetry\SDK\Logs\EventLoggerProviderFactory;
use OpenTelemetry\SDK\Logs\LoggerProviderFactory;
use OpenTelemetry\SDK\Metrics\MeterProviderFactory;
use OpenTelemetry\SDK\Propagation\LateBindingResponsePropagator;
use OpenTelemetry\SDK\Propagation\LateBindingTextMapPropagator;
use OpenTelemetry\SDK\Propagation\PropagatorFactory;
use OpenTelemetry\SDK\Propagation\ResponsePropagatorFactory;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\AutoRootSpan;
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

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public static function autoload(): bool
    {
        if (!self::isEnabled() || self::isExcludedUrl()) {
            return false;
        }
        $config = null;
        if (Configuration::has(Variables::OTEL_CONFIG_FILE)) {
            if (!class_exists(SdkConfiguration::class)) {
                self::logWarning('Error during opentelemetry initialization: file-based configuration requires open-telemetry/sdk-configuration');
            } else {
                try {
                    // One parse serves both configuration roots. Registering no initializer on
                    // failure leaves Globals to fall back to its noop configurator.
                    $config = SdkConfiguration::parseFile(Configuration::getList(Variables::OTEL_CONFIG_FILE));
                    Globals::registerInitializer(fn ($configurator) => self::fileBasedInitializer($configurator, $config));
                } catch (Throwable $t) {
                    self::logWarning(sprintf('Error during opentelemetry initialization: %s', $t->getMessage()), ['exception' => $t]);
                }
            }
        } else {
            Globals::registerInitializer(fn ($configurator) => self::environmentBasedInitializer($configurator));
        }
        self::registerInstrumentations($config);

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
     * @phan-suppress PhanDeprecatedClass,PhanDeprecatedFunction
     */
    private static function environmentBasedInitializer(Configurator $configurator): Configurator
    {
        $propagator = (new PropagatorFactory())->create();
        $responsePropagator = (new ResponsePropagatorFactory())->create();
        if (Sdk::isDisabled()) {
            //@see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#general-sdk-configuration
            return $configurator->withPropagator($propagator)->withResponsePropagator($responsePropagator);
        }
        $distributionProperties = self::loadDistributionPropertiesFromEnv();
        $distributionConfiguration = $distributionProperties->getDistributionConfiguration(SdkDistribution::class) ?? new SdkDistribution();
        $emitMetrics = $distributionConfiguration->internalMetricsEnabled;

        $resource = ResourceInfoFactory::defaultResource();
        $exporter = (new ExporterFactory())->create();
        $meterProvider = (new MeterProviderFactory())->create($resource);
        $spanProcessor = (new SpanProcessorFactory())->create($exporter, $emitMetrics ? $meterProvider : null);
        $builder = (new TracerProviderBuilder())
            ->addSpanProcessor($spanProcessor)
            ->setResource($resource)
            ->setSampler((new SamplerFactory())->create())
            ->setSpanSuppressionStrategy($distributionConfiguration->spanSuppressionStrategy);
        if ($emitMetrics) {
            $builder->setMeterProvider($meterProvider);
        }
        $tracerProvider = $builder->build();

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
            ->withResponsePropagator($responsePropagator)
        ;
    }

    /**
     * @phan-suppress PhanPossiblyUndeclaredVariable,PhanDeprecatedFunction
     */
    private static function fileBasedInitializer(Configurator $configurator, SdkConfiguration $config): Configurator
    {
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
     *
     * Instrumentation config is resolved here, during autoload, because hooks must be installed
     * before the instrumented function is first called, whereas building the SDK is deferred to a
     * Globals initializer. (With {@see AutoRootSpan} enabled the initializer runs before autoload
     * returns, so the deferral is conditional.) Both are served by the one document `$config`
     * already holds.
     */
    private static function registerInstrumentations(?SdkConfiguration $config): void
    {
        if (!Configuration::has(Variables::OTEL_CONFIG_FILE)) {
            $configuration = self::loadConfigPropertiesFromEnv();
        } else {
            try {
                // Catching here, rather than letting this join the parse above, is what keeps an
                // unparseable instrumentation node from also costing us the already-registered SDK.
                $configuration = $config?->getInstrumentationConfig() ?? new NoopConfigProperties();
            } catch (Error $t) {
                // An Error here means the installed sdk-configuration does not offer the API we
                // call, rather than that the file is wrong: `sdk` can only suggest that package, so
                // its version cannot be constrained from here and the two are released separately.
                self::logWarning(sprintf('Error during opentelemetry initialization: instrumentation configuration requires a newer open-telemetry/sdk-configuration: %s', $t->getMessage()), ['exception' => $t]);
                $configuration = new NoopConfigProperties();
            } catch (Throwable $t) {
                self::logWarning(sprintf('Error during opentelemetry initialization: %s', $t->getMessage()), ['exception' => $t]);
                $configuration = new NoopConfigProperties();
            }
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

    private static function loadDistributionPropertiesFromEnv(): DistributionProperties
    {
        $loaderRegistry = new EnvComponentLoaderRegistry();
        foreach (ServiceLoader::load(EnvComponentLoader::class) as $loader) {
            $loaderRegistry->register($loader);
        }

        $env = new EnvResolver();
        $context = new \OpenTelemetry\API\Configuration\Context();

        $configuration = new DistributionRegistry();
        foreach ($loaderRegistry->loadAll(DistributionConfiguration::class, $env, $context) as $distribution) {
            $configuration->add($distribution);
        }

        return $configuration;
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
