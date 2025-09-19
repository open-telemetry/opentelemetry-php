<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

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
 * @todo [breaking] deprecate this mechanism of setting up components, in favor of using SPI.
 */
class Registry
{
    private static array $spanExporterFactories = [];
    private static array $transportFactories = [];
    private static array $metricExporterFactories = [];
    private static array $textMapPropagators = [];
    private static array $logRecordExporterFactories = [];
    private static array $resourceDetectors = [];
    private static array $responsePropagators = [];

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
        if (!array_key_exists($exporter, self::$spanExporterFactories)) {
            throw new RuntimeException('Span exporter factory not defined for: ' . $exporter);
        }
        $class = self::$spanExporterFactories[$exporter];
        $factory = (is_callable($class)) ? $class : new $class();
        assert($factory instanceof SpanExporterFactoryInterface);

        return $factory;
    }

    public static function logRecordExporterFactory(string $exporter): LogRecordExporterFactoryInterface
    {
        if (!array_key_exists($exporter, self::$logRecordExporterFactories)) {
            throw new RuntimeException('LogRecord exporter factory not defined for: ' . $exporter);
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
        if (!array_key_exists($protocol, self::$transportFactories)) {
            throw new RuntimeException('Transport factory not defined for protocol: ' . $protocol);
        }
        $class = self::$transportFactories[$protocol];
        $factory = (is_callable($class)) ? $class : new $class();
        assert($factory instanceof TransportFactoryInterface);

        return $factory;
    }

    public static function metricExporterFactory(string $exporter): MetricExporterFactoryInterface
    {
        if (!array_key_exists($exporter, self::$metricExporterFactories)) {
            throw new RuntimeException('Metric exporter factory not registered for protocol: ' . $exporter);
        }
        $class = self::$metricExporterFactories[$exporter];
        $factory = (is_callable($class)) ? $class : new $class();
        assert($factory instanceof MetricExporterFactoryInterface);

        return $factory;
    }

    public static function textMapPropagator(string $name): TextMapPropagatorInterface
    {
        if (!array_key_exists($name, self::$textMapPropagators)) {
            throw new RuntimeException('Text map propagator not registered for: ' . $name);
        }

        return self::$textMapPropagators[$name];
    }

    public static function resourceDetector(string $name): ResourceDetectorInterface
    {
        if (!array_key_exists($name, self::$resourceDetectors)) {
            throw new RuntimeException('Resource detector not registered for: ' . $name);
        }

        return self::$resourceDetectors[$name];
    }

    public static function responsePropagator(string $name): ResponsePropagatorInterface
    {
        if (!array_key_exists($name, self::$responsePropagators)) {
            throw new RuntimeException('Response propagator not registered for: ' . $name);
        }

        return self::$responsePropagators[$name];
    }

    /**
     * @return array<int, ResourceDetectorInterface>
     */
    public static function resourceDetectors(): array
    {
        return array_values(self::$resourceDetectors);
    }
}
