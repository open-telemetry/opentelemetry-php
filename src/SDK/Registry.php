<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Resource\ResourceDetectorFactoryInterface;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use RuntimeException;
use TypeError;

/**
 * A registry to enable central registration of components that the SDK requires but which may be provided
 * by non-SDK modules, such as contrib and extension.
 * @phan-file-suppress PhanTypeMismatchProperty
 * @todo [breaking] deprecate this mechanism of setting up components, in favor of using SPI.
 */
class Registry
{
    /** @var array<string, SpanExporterFactoryInterface> $spanExporterFactories */
    private static array $spanExporterFactories = [];
    /** @var array<string, TransportFactoryInterface> $transportFactories */
    private static array $transportFactories = [];
    /** @var array<string, MetricExporterFactoryInterface> $metricExporterFactories */
    private static array $metricExporterFactories = [];
    /** @var array<string, LogRecordExporterFactoryInterface> $logRecordExporterFactories */
    private static array $logRecordExporterFactories = [];

    /** @var array<string, TextMapPropagatorInterface> $textMapPropagators */
    private static array $textMapPropagators = [];
    /** @var array<string, ResourceDetectorInterface> $resourceDetectors */
    private static array $resourceDetectors = [];

    /**
     * @param class-string $class
     * @phan-suppress PhanTypeNonVarPassByRef
     */
    private static function getFactories(string $class): array
    {
        $factories = iterator_to_array(ServiceLoader::load($class));
        array_multisort(
            array_map(static fn ($factory) => $factory->priority(), $factories),
            SORT_DESC,
            $factories,
        );
        $factoriesByType = [];
        foreach ($factories as $factory) {
            $factoriesByType[$factory->type()] ??= $factory;
        }

        return $factoriesByType;
    }

    /**
     * @param class-string $class
     * @phan-suppress PhanTypeNonVarPassByRef
     */
    private static function getFactoryInstances(string $class): array
    {
        $factories = iterator_to_array(ServiceLoader::load($class));
        array_multisort(
            array_map(static fn ($factory) => $factory->priority(), $factories),
            SORT_DESC,
            $factories,
        );
        $instances = [];
        foreach ($factories as $factory) {
            $instances[$factory->type()] ??= $factory->create();
        }

        return $instances;
    }

    /**
     * @param TransportFactoryInterface|class-string<TransportFactoryInterface> $factory
     * @throws TypeError
     */
    public static function registerTransportFactory(string $protocol, TransportFactoryInterface|string $factory, bool $clobber = false): void
    {
        if (Sdk::useSpiRegistry()) {
            return;
        }
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
        if (Sdk::useSpiRegistry()) {
            return;
        }
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
        if (Sdk::useSpiRegistry()) {
            return;
        }
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
        if (Sdk::useSpiRegistry()) {
            return;
        }
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
        if (Sdk::useSpiRegistry()) {
            return;
        }
        if (!$clobber && array_key_exists($name, self::$textMapPropagators)) {
            return;
        }
        self::$textMapPropagators[$name] = $propagator;
    }

    public static function registerResourceDetector(string $name, ResourceDetectorInterface $detector): void
    {
        if (Sdk::useSpiRegistry()) {
            return;
        }
        self::$resourceDetectors[$name] = $detector;
    }

    /**
     * @phan-suppress PhanNonClassMethodCall,PhanTypeExpectedObjectOrClassName
     */
    public static function spanExporterFactory(string $exporter): SpanExporterFactoryInterface
    {
        if (Sdk::useSpiRegistry()) {
            $factories = self::getFactories(SpanExporterFactoryInterface::class);
        } else {
            $factories = self::$spanExporterFactories;
        }
        if (!array_key_exists($exporter, $factories)) {
            throw new RuntimeException('Span exporter factory not defined for: ' . $exporter);
        }
        $class = $factories[$exporter];
        $factory = (is_callable($class)) ? $class : new $class();
        assert($factory instanceof SpanExporterFactoryInterface);

        return $factory;
    }

    /**
     * @phan-suppress PhanNonClassMethodCall,PhanTypeExpectedObjectOrClassName
     */
    public static function logRecordExporterFactory(string $exporter): LogRecordExporterFactoryInterface
    {
        if (Sdk::useSpiRegistry()) {
            $factories = self::getFactories(LogRecordExporterFactoryInterface::class);
        } else {
            $factories = self::$logRecordExporterFactories;
        }
        if (!array_key_exists($exporter, $factories)) {
            throw new RuntimeException('LogRecord exporter factory not defined for: ' . $exporter);
        }
        $class = $factories[$exporter];
        $factory = (is_callable($class)) ? $class : new $class();
        assert($factory instanceof LogRecordExporterFactoryInterface);

        return $factory;
    }

    /**
     * Get transport factory registered for protocol. If $protocol contains a content-type eg `http/xyz` then
     * only the first part, `http`, is used.
     * @phan-suppress PhanNonClassMethodCall,PhanTypeExpectedObjectOrClassName
     */
    public static function transportFactory(string $protocol): TransportFactoryInterface
    {
        if (Sdk::useSpiRegistry()) {
            $factories = self::getFactories(TransportFactoryInterface::class);
        } else {
            $factories = self::$transportFactories;
        }
        $protocol = explode('/', $protocol)[0];
        if (!array_key_exists($protocol, $factories)) {
            throw new RuntimeException('Transport factory not defined for protocol: ' . $protocol);
        }
        $class = $factories[$protocol];
        $factory = (is_callable($class)) ? $class : new $class();
        assert($factory instanceof TransportFactoryInterface);

        return $factory;
    }

    /**
     * @phan-suppress PhanNonClassMethodCall,PhanTypeExpectedObjectOrClassName
     */
    public static function metricExporterFactory(string $exporter): MetricExporterFactoryInterface
    {
        if (Sdk::useSpiRegistry()) {
            $factories = self::getFactories(MetricExporterFactoryInterface::class);
        } else {
            $factories = self::$metricExporterFactories;
        }
        if (!array_key_exists($exporter, $factories)) {
            throw new RuntimeException('Metric exporter factory not registered for protocol: ' . $exporter);
        }
        $class = $factories[$exporter];
        $factory = (is_callable($class)) ? $class : new $class();
        assert($factory instanceof MetricExporterFactoryInterface);

        return $factory;
    }

    public static function textMapPropagator(string $name): TextMapPropagatorInterface
    {
        if (Sdk::useSpiRegistry()) {
            $propagators = self::getFactoryInstances(TextMapPropagatorInterface::class);
        } else {
            $propagators = self::$textMapPropagators;
        }
        if (!array_key_exists($name, $propagators)) {
            throw new RuntimeException('Text map propagator not registered for: ' . $name);
        }

        return $propagators[$name];
    }

    public static function resourceDetector(string $name): ResourceDetectorInterface
    {
        if (Sdk::useSpiRegistry()) {
            $detectors = self::getFactoryInstances(ResourceDetectorFactoryInterface::class);
        } else {
            $detectors = self::$resourceDetectors;
        }
        if (!array_key_exists($name, $detectors)) {
            throw new RuntimeException('Resource detector not registered for: ' . $name);
        }

        return $detectors[$name];
    }

    /**
     * @return array<int, ResourceDetectorInterface>
     */
    public static function resourceDetectors(): array
    {
        if (Sdk::useSpiRegistry()) {
            $detectors = self::getFactoryInstances(ResourceDetectorFactoryInterface::class);
        } else {
            $detectors = self::$resourceDetectors;
        }

        return array_values($detectors);
    }
}
