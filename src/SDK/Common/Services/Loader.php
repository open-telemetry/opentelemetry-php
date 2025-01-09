<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Services;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Resource\ResourceDetectorFactoryInterface;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use RuntimeException;

/**
 * Loads services from SPI, handling type and priority.
 */
class Loader
{
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

    public static function spanExporterFactory(string $exporter): SpanExporterFactoryInterface
    {
        $factories = self::getFactories(SpanExporterFactoryInterface::class);
        if (!array_key_exists($exporter, $factories)) {
            throw new RuntimeException('Span exporter factory not defined for: ' . $exporter);
        }
        $class = $factories[$exporter];
        $factory = new $class();
        assert($factory instanceof SpanExporterFactoryInterface);

        return $factory;
    }

    public static function logRecordExporterFactory(string $exporter): LogRecordExporterFactoryInterface
    {
        $factories = self::getFactories(LogRecordExporterFactoryInterface::class);
        if (!array_key_exists($exporter, $factories)) {
            throw new RuntimeException('LogRecord exporter factory not defined for: ' . $exporter);
        }
        $class = $factories[$exporter];
        $factory = new $class();
        assert($factory instanceof LogRecordExporterFactoryInterface);

        return $factory;
    }

    /**
     * Get transport factory registered for protocol. If $protocol contains a content-type eg `http/xyz` then
     * only the first part, `http`, is used.
     */
    public static function transportFactory(string $protocol): TransportFactoryInterface
    {
        $factories = self::getFactories(TransportFactoryInterface::class);
        $protocol = explode('/', $protocol)[0];
        if (!array_key_exists($protocol, $factories)) {
            throw new RuntimeException('Transport factory not defined for protocol: ' . $protocol);
        }
        $class = $factories[$protocol];
        $factory = new $class();
        assert($factory instanceof TransportFactoryInterface);

        return $factory;
    }

    public static function metricExporterFactory(string $exporter): MetricExporterFactoryInterface
    {
        $factories = self::getFactories(MetricExporterFactoryInterface::class);
        if (!array_key_exists($exporter, $factories)) {
            throw new RuntimeException('Metric exporter factory not registered for protocol: ' . $exporter);
        }
        $class = $factories[$exporter];
        $factory = new $class();
        assert($factory instanceof MetricExporterFactoryInterface);

        return $factory;
    }

    public static function textMapPropagator(string $name): TextMapPropagatorInterface
    {
        $factories = self::getFactories(TextMapPropagatorFactoryInterface::class);
        if (!array_key_exists($name, $factories)) {
            throw new RuntimeException('Text map propagator not registered for: ' . $name);
        }
        $factory = $factories[$name];

        return $factory->create();
    }

    public static function resourceDetector(string $name): ResourceDetectorInterface
    {
        $factories = self::getFactories(ResourceDetectorFactoryInterface::class);
        if (!array_key_exists($name, $factories)) {
            throw new RuntimeException('Resource detector not registered for: ' . $name);
        }
        $factory = $factories[$name];

        return $factory->create();
    }

    /**
     * @return array<int, ResourceDetectorInterface>
     */
    public static function resourceDetectors(): array
    {
        $factories = self::getFactories(ResourceDetectorFactoryInterface::class);
        $instances = [];
        foreach ($factories as $factory) {
            $instances[$factory->type()] ??= $factory->create();
        }

        return array_values($instances);
    }
}
