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

    /**
     * @param callable-object|class-string $factory
     * @psalm-suppress PossiblyInvalidArgument
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
                    is_string($factory) ? $factory : 'callable',
                    TransportFactoryInterface::class
                ),
                E_USER_WARNING
            );

            return;
        }
        self::$transportFactories[$protocol] = $factory;
    }

    /**
     * @param callable-object|class-string $factory
     */
    public static function registerSpanExporterFactory(string $protocol, $factory, bool $clobber = false): void
    {
        if (!$clobber && array_key_exists($protocol, self::$spanExporterFactories)) {
            return;
        }
        if (!self::check($factory, SpanExporterFactoryInterface::class)) {
            trigger_error(
                sprintf(
                    'Cannot register span exporter factory: %s must exist and implement %s',
                    is_string($factory) ? $factory : 'callable',
                    SpanExporterFactoryInterface::class
                ),
                E_USER_WARNING
            );

            return;
        }
        self::$spanExporterFactories[$protocol] = $factory;
    }

    /**
     * @param callable-object|class-string $factory
     */
    public static function registerMetricExporterFactory(string $protocol, $factory, bool $clobber = false): void
    {
        if (!$clobber && array_key_exists($protocol, self::$metricExporterFactories)) {
            return;
        }
        if (!self::check($factory, MetricExporterFactoryInterface::class)) {
            trigger_error(
                sprintf(
                    'Cannot register metric factory: %s must exist and implement %s',
                    is_string($factory) ? $factory : 'callable',
                    MetricExporterFactoryInterface::class
                ),
                E_USER_WARNING
            );

            return;
        }
        self::$metricExporterFactories[$protocol] = $factory;
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
        $factory = (is_callable($class)) ? $class : new $class();
        assert($factory instanceof SpanExporterFactoryInterface);

        return $factory;
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
        $factory = (is_callable($class)) ? $class : new $class();
        assert($factory instanceof TransportFactoryInterface);

        return $factory;
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
        $factory = (is_callable($class)) ? $class : new $class();
        assert($factory instanceof MetricExporterFactoryInterface);

        return $factory;
    }

    private static function check($class, string $interface): bool
    {
        if (!class_exists($class)) {
            return false;
        }
        $implements = class_implements($class);

        return in_array($interface, $implements);
    }
}
