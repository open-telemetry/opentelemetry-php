<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use RuntimeException;

class Registry
{
    private static array $spanExporterFactories = [];
    private static array $transportFactories = [];
    private static array $metricExporterFactories = [];

    public static function registerTransportFactory(string $protocol, string $factoryClass): void
    {
        if (!is_subclass_of($factoryClass, TransportFactoryInterface::class)) {
            trigger_error(
                sprintf('Cannot register transport factory: %s must exist and implement %s', $factoryClass, TransportFactoryInterface::class),
                E_USER_WARNING
            );

            return;
        }
        self::$transportFactories[$protocol] = $factoryClass;
    }

    public static function registerSpanExporterFactory(string $protocol, string $factoryClass): void
    {
        if (!self::check($factoryClass, SpanExporterFactoryInterface::class)) {
            trigger_error(
                sprintf('Cannot register span exporter factory: %s must exist and implement %s', $factoryClass, SpanExporterFactoryInterface::class),
                E_USER_WARNING
            );

            return;
        }
        self::$spanExporterFactories[$protocol] = $factoryClass;
    }

    public static function registerMetricExporterFactory(string $protocol, string $factoryClass): void
    {
        if (!self::check($factoryClass, MetricExporterFactoryInterface::class)) {
            trigger_error(
                sprintf('Cannot register metric factory: %s must exist and implement %s', $factoryClass, MetricExporterFactoryInterface::class),
                E_USER_WARNING
            );

            return;
        }
        self::$metricExporterFactories[$protocol] = $factoryClass;
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    public static function spanExporterFactory(string $protocol): SpanExporterFactoryInterface
    {
        if (!array_key_exists($protocol, self::$spanExporterFactories)) {
            throw new RuntimeException('Span exporter factory not defined for: ' . $protocol);
        }
        $class = self::$spanExporterFactories[$protocol];

        return new $class();
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    public static function transportFactory(string $protocol): TransportFactoryInterface
    {
        if (!array_key_exists($protocol, self::$transportFactories)) {
            throw new RuntimeException('Transport factory not defined for protocol: ' . $protocol);
        }
        $class = self::$transportFactories[$protocol];

        return new $class();
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    public static function metricExporterFactory(string $protocol): MetricExporterFactoryInterface
    {
        if (!array_key_exists($protocol, self::$metricExporterFactories)) {
            throw new RuntimeException('Metric exporter factory not registered for protocol: ' . $protocol);
        }
        $class = self::$metricExporterFactories[$protocol];

        return new $class();
    }

    private static function check(string $class, string $interface): bool
    {
        if (!class_exists($class)) {
            return false;
        }
        $implements = class_implements($class);

        return in_array($interface, $implements);
    }
}
