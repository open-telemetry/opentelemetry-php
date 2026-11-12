<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use RuntimeException;
use TypeError;

/**
 * A registry to enable central registration of components that the SDK requires but which may be provided
 * by non-SDK modules, such as contrib and extension.
 *
 * Components are registered either explicitly, with the `register*()` methods, or by a {@see RegistrarInterface}
 * discovered through SPI. Registrars are loaded lazily on the first lookup, so the result does not depend on the order
 * in which Composer includes `autoload.files`. A component registered explicitly before that wins over one contributed
 * by a registrar, and one registered with `$clobber` always wins.
 *
 * @todo [breaking] deprecate the explicit register*() methods, in favor of using SPI.
 */
class Registry
{
    use LogsMessagesTrait;

    private static array $spanExporterFactories = [];
    private static array $transportFactories = [];
    private static array $metricExporterFactories = [];
    private static array $textMapPropagators = [];
    private static array $logRecordExporterFactories = [];
    private static array $resourceDetectors = [];
    private static array $responsePropagators = [];
    private static bool $registrarsLoaded = false;
    private static int $registrarCount = 0;
    /** @var array<class-string<RegistrarInterface>, true> */
    private static array $registrarsRun = [];

    /**
     * @param TransportFactoryInterface|class-string<TransportFactoryInterface> $factory
     * @throws TypeError
     */
    public static function registerTransportFactory(string $protocol, TransportFactoryInterface|string $factory, bool $clobber = false): void
    {
        if (!$clobber && array_key_exists($protocol, self::$transportFactories)) {
            return;
        }
        if (!is_subclass_of($factory, TransportFactoryInterface::class)) {
            throw new TypeError(
                sprintf(
                    'Cannot register transport factory: %s must exist and implement %s',
                    is_string($factory) ? $factory : $factory::class,
                    TransportFactoryInterface::class
                )
            );
        }
        self::$transportFactories[$protocol] = $factory;
    }

    /**
     * @param SpanExporterFactoryInterface|class-string<SpanExporterFactoryInterface> $factory
     * @throws TypeError
     */
    public static function registerSpanExporterFactory(string $exporter, SpanExporterFactoryInterface|string $factory, bool $clobber = false): void
    {
        if (!$clobber && array_key_exists($exporter, self::$spanExporterFactories)) {
            return;
        }
        if (!is_subclass_of($factory, SpanExporterFactoryInterface::class)) {
            throw new TypeError(
                sprintf(
                    'Cannot register span exporter factory: %s must exist and implement %s',
                    is_string($factory) ? $factory : $factory::class,
                    SpanExporterFactoryInterface::class
                )
            );
        }
        self::$spanExporterFactories[$exporter] = $factory;
    }

    /**
     * @param MetricExporterFactoryInterface|class-string<MetricExporterFactoryInterface> $factory
     * @throws TypeError
     */
    public static function registerMetricExporterFactory(string $exporter, MetricExporterFactoryInterface|string $factory, bool $clobber = false): void
    {
        if (!$clobber && array_key_exists($exporter, self::$metricExporterFactories)) {
            return;
        }
        if (!is_subclass_of($factory, MetricExporterFactoryInterface::class)) {
            throw new TypeError(
                sprintf(
                    'Cannot register metric factory: %s must exist and implement %s',
                    is_string($factory) ? $factory : $factory::class,
                    MetricExporterFactoryInterface::class
                )
            );
        }
        self::$metricExporterFactories[$exporter] = $factory;
    }

    /**
     * @param LogRecordExporterFactoryInterface|class-string<LogRecordExporterFactoryInterface> $factory
     * @throws TypeError
     */
    public static function registerLogRecordExporterFactory(string $exporter, LogRecordExporterFactoryInterface|string $factory, bool $clobber = false): void
    {
        if (!$clobber && array_key_exists($exporter, self::$logRecordExporterFactories)) {
            return;
        }
        if (!is_subclass_of($factory, LogRecordExporterFactoryInterface::class)) {
            throw new TypeError(
                sprintf(
                    'Cannot register LogRecord exporter factory: %s must exist and implement %s',
                    is_string($factory) ? $factory : $factory::class,
                    LogRecordExporterFactoryInterface::class
                )
            );
        }
        self::$logRecordExporterFactories[$exporter] = $factory;
    }

    public static function registerTextMapPropagator(string $name, TextMapPropagatorInterface $propagator, bool $clobber = false): void
    {
        if (!$clobber && array_key_exists($name, self::$textMapPropagators)) {
            return;
        }
        self::$textMapPropagators[$name] = $propagator;
    }

    public static function registerResourceDetector(string $name, ResourceDetectorInterface $detector): void
    {
        self::$resourceDetectors[$name] = $detector;
    }

    public static function registerResponsePropagator(string $name, ResponsePropagatorInterface $responsePropagator, bool $clobber = false): void
    {
        if (!$clobber && array_key_exists($name, self::$responsePropagators)) {
            return;
        }
        self::$responsePropagators[$name] = $responsePropagator;
    }

    public static function spanExporterFactory(string $exporter): SpanExporterFactoryInterface
    {
        if (!self::isRegistered(self::$spanExporterFactories, $exporter)) {
            throw self::notRegistered('Span exporter factory not defined for: ' . $exporter);
        }
        $class = self::$spanExporterFactories[$exporter];
        $factory = (is_callable($class)) ? $class : new $class();
        assert($factory instanceof SpanExporterFactoryInterface);

        return $factory;
    }

    public static function logRecordExporterFactory(string $exporter): LogRecordExporterFactoryInterface
    {
        if (!self::isRegistered(self::$logRecordExporterFactories, $exporter)) {
            throw self::notRegistered('LogRecord exporter factory not defined for: ' . $exporter);
        }
        $class = self::$logRecordExporterFactories[$exporter];
        $factory = (is_callable($class)) ? $class : new $class();
        assert($factory instanceof LogRecordExporterFactoryInterface);

        return $factory;
    }

    /**
     * Get transport factory registered for protocol. If $protocol contains a content-type eg `http/xyz` then
     * only the first part, `http`, is used.
     */
    public static function transportFactory(string $protocol): TransportFactoryInterface
    {
        $protocol = explode('/', $protocol)[0];
        if (!self::isRegistered(self::$transportFactories, $protocol)) {
            throw self::notRegistered('Transport factory not defined for protocol: ' . $protocol);
        }
        $class = self::$transportFactories[$protocol];
        $factory = (is_callable($class)) ? $class : new $class();
        assert($factory instanceof TransportFactoryInterface);

        return $factory;
    }

    public static function metricExporterFactory(string $exporter): MetricExporterFactoryInterface
    {
        if (!self::isRegistered(self::$metricExporterFactories, $exporter)) {
            throw self::notRegistered('Metric exporter factory not registered for protocol: ' . $exporter);
        }
        $class = self::$metricExporterFactories[$exporter];
        $factory = (is_callable($class)) ? $class : new $class();
        assert($factory instanceof MetricExporterFactoryInterface);

        return $factory;
    }

    public static function textMapPropagator(string $name): TextMapPropagatorInterface
    {
        if (!self::isRegistered(self::$textMapPropagators, $name)) {
            throw self::notRegistered('Text map propagator not registered for: ' . $name);
        }

        return self::$textMapPropagators[$name];
    }

    public static function resourceDetector(string $name): ResourceDetectorInterface
    {
        if (!self::isRegistered(self::$resourceDetectors, $name)) {
            throw self::notRegistered('Resource detector not registered for: ' . $name);
        }

        return self::$resourceDetectors[$name];
    }

    public static function responsePropagator(string $name): ResponsePropagatorInterface
    {
        if (!self::isRegistered(self::$responsePropagators, $name)) {
            throw self::notRegistered('Response propagator not registered for: ' . $name);
        }

        return self::$responsePropagators[$name];
    }

    /**
     * @return array<int, ResourceDetectorInterface>
     */
    public static function resourceDetectors(): array
    {
        self::loadRegistrars();

        return array_values(self::$resourceDetectors);
    }

    /**
     * Whether a component is registered. The first lookup runs the registrars. On a miss they are looked up again, to
     * pick up registrars that were registered with the service loader after the first lookup. That happens without
     * the `tbachert/spi` Composer plugin, when `_register.php` files are included one after the other.
     *
     * @param array<string, mixed> $registered
     */
    private static function isRegistered(array &$registered, string $key): bool
    {
        self::loadRegistrars();
        if (array_key_exists($key, $registered)) {
            return true;
        }
        self::loadRegistrars(true);

        return array_key_exists($key, $registered);
    }

    /**
     * Runs the {@see RegistrarInterface} providers that were not run yet. A registrar that fails is logged and skipped,
     * it must not keep the others from registering their components.
     */
    private static function loadRegistrars(bool $rescan = false): void
    {
        if (self::$registrarsLoaded && !$rescan) {
            return;
        }
        self::$registrarsLoaded = true;
        $registrars = ServiceLoader::load(RegistrarInterface::class)->getIterator();
        for (; $registrars->valid(); $registrars->next()) {
            try {
                $registrar = $registrars->current();
                if ($registrar === null || isset(self::$registrarsRun[$registrar::class])) {
                    continue;
                }
                self::$registrarsRun[$registrar::class] = true;
                self::$registrarCount++;
                $registrar->register();
            } catch (\Throwable $e) {
                self::logError('Unable to run registrar', ['exception' => $e]);
            }
        }
    }

    /**
     * Components are contributed by {@see RegistrarInterface} providers, which only become known through the generated
     * SPI data of the `tbachert/spi` Composer plugin. Without the plugin a component can be missing for as long as the
     * `autoload.files` that would register it have not been included, for example while the SDK autoloads.
     */
    private static function notRegistered(string $message): RuntimeException
    {
        if (self::$registrarCount === 0 || !class_exists('Nevay\\SPI\\GeneratedServiceProviderData')) {
            $message .= '. If the component is provided by an installed package, make sure the tbachert/spi Composer'
                . ' plugin is allowed (config.allow-plugins) and ran, without it the registration depends on the order'
                . ' in which Composer includes the autoload files of the packages.';
        }

        return new RuntimeException($message);
    }
}
