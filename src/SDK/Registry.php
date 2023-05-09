<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use RuntimeException;

/**
 * A registry to enable central registration of components that the SDK requires but which may be provided
 * by non-SDK modules, such as contrib and extension.
 */
class Registry
{
    private static array $spanExporterFactories = [];
    private static array $transportFactories = [];
    private static array $metricExporterFactories = [];
    private static array $textMapPropagators = [];
    private static array $logRecordExporterFactories = [];
    private static array $resourceDetectors = [];

    /**
     * @param TransportFactoryInterface|class-string<TransportFactoryInterface> $factory
     */
    public static function registerTransportFactory(string $protocol, $factory, bool $clobber = false): void
    {
        if (!$clobber && array_key_exists($protocol, self::$transportFactories)) {
            return;
        }
        if (!is_subclass_of($factory, TransportFactoryInterface::class)) {
            trigger_error(
                sprintf(
                    'Cannot register transport factory: %s must exist and implement %s',
                    is_string($factory) ? $factory : get_class($factory),
                    TransportFactoryInterface::class
                ),
                E_USER_WARNING
            );

            return;
        }
        self::$transportFactories[$protocol] = $factory;
    }

    /**
     * @param SpanExporterFactoryInterface|class-string<SpanExporterFactoryInterface> $factory
     */
    public static function registerSpanExporterFactory(string $exporter, $factory, bool $clobber = false): void
    {
        if (!$clobber && array_key_exists($exporter, self::$spanExporterFactories)) {
            return;
        }
        if (!is_subclass_of($factory, SpanExporterFactoryInterface::class)) {
            trigger_error(
                sprintf(
                    'Cannot register span exporter factory: %s must exist and implement %s',
                    is_string($factory) ? $factory : get_class($factory),
                    SpanExporterFactoryInterface::class
                ),
                E_USER_WARNING
            );

            return;
        }
        self::$spanExporterFactories[$exporter] = $factory;
    }

    /**
     * @param MetricExporterFactoryInterface|class-string<MetricExporterFactoryInterface> $factory
     */
    public static function registerMetricExporterFactory(string $exporter, $factory, bool $clobber = false): void
    {
        if (!$clobber && array_key_exists($exporter, self::$metricExporterFactories)) {
            return;
        }
        if (!is_subclass_of($factory, MetricExporterFactoryInterface::class)) {
            trigger_error(
                sprintf(
                    'Cannot register metric factory: %s must exist and implement %s',
                    is_string($factory) ? $factory : get_class($factory),
                    MetricExporterFactoryInterface::class
                ),
                E_USER_WARNING
            );

            return;
        }
        self::$metricExporterFactories[$exporter] = $factory;
    }

    public static function registerLogRecordExporterFactory(string $exporter, $factory, bool $clobber = false): void
    {
        if (!$clobber && array_key_exists($exporter, self::$logRecordExporterFactories)) {
            return;
        }
        if (!is_subclass_of($factory, LogRecordExporterFactoryInterface::class)) {
            trigger_error(
                sprintf(
                    'Cannot register LogRecord exporter factory: %s must exist and implement %s',
                    is_string($factory) ? $factory : get_class($factory),
                    LogRecordExporterFactoryInterface::class
                ),
                E_USER_WARNING
            );

            return;
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

    /**
     * @return array<int, ResourceDetectorInterface>
     */
    public static function resourceDetectors(): array
    {
        return array_values(self::$resourceDetectors);
    }
}
