<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

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
use TypeError;

/**
 * A registry to enable central registration of components that the SDK requires but which may be provided
 * by non-SDK modules, such as contrib and extension.
 * @phan-file-suppress PhanTypeMismatchProperty
 * @todo [breaking] deprecate this mechanism of setting up components, in favor of using SPI.
 */
class Registry
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
     * @deprecated Register via SPI instead
     */
    public static function registerTransportFactory(string $protocol, TransportFactoryInterface|string $factory, bool $clobber = false): void
    {

    }

    /**
     * @param SpanExporterFactoryInterface|class-string<SpanExporterFactoryInterface> $factory
     * @throws TypeError
     * @deprecated Register via SPI instead
     */
    public static function registerSpanExporterFactory(string $exporter, SpanExporterFactoryInterface|string $factory, bool $clobber = false): void
    {

    }

    /**
     * @param MetricExporterFactoryInterface|class-string<MetricExporterFactoryInterface> $factory
     * @throws TypeError
     * @deprecated Register via SPI instead
     */
    public static function registerMetricExporterFactory(string $exporter, MetricExporterFactoryInterface|string $factory, bool $clobber = false): void
    {

    }

    /**
     * @param LogRecordExporterFactoryInterface|class-string<LogRecordExporterFactoryInterface> $factory
     * @throws TypeError
     * @deprecated Register via SPI instead
     */
    public static function registerLogRecordExporterFactory(string $exporter, LogRecordExporterFactoryInterface|string $factory, bool $clobber = false): void
    {

    }

    /**
     * @deprecated Register via SPI instead
     */
    public static function registerTextMapPropagator(string $name, TextMapPropagatorInterface $propagator, bool $clobber = false): void
    {

    }

    /**
     * @deprecated Register via SPI instead
     */
    public static function registerResourceDetector(string $name, ResourceDetectorInterface $detector): void
    {
        $factory = new class($name, $detector) implements ResourceDetectorFactoryInterface {
            private readonly string $name;
            private readonly ResourceDetectorInterface $detector;
            public function __construct(string $name, ResourceDetectorInterface $detector)
            {
                $this->name = $name;
                $this->detector = $detector;
            }
            public function create(): ResourceDetectorInterface
            {
                return $this->detector;
            }

            public function type(): string
            {
                return $this->name;
            }

            public function priority(): int
            {
                return 1; //higher than default
            }
        };
        ServiceLoader::register(ResourceDetectorFactoryInterface::class, $factory::class);
    }

    /**
     * @phan-suppress PhanNonClassMethodCall,PhanTypeExpectedObjectOrClassName
     */
    public static function spanExporterFactory(string $exporter): SpanExporterFactoryInterface
    {
        $factories = self::getFactories(SpanExporterFactoryInterface::class);
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
        $factories = self::getFactories(LogRecordExporterFactoryInterface::class);
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
        $factories = self::getFactories(TransportFactoryInterface::class);
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
        $factories = self::getFactories(MetricExporterFactoryInterface::class);
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
        $propagators = self::getFactoryInstances(TextMapPropagatorFactoryInterface::class);
        if (!array_key_exists($name, $propagators)) {
            throw new RuntimeException('Text map propagator not registered for: ' . $name);
        }

        return $propagators[$name];
    }

    public static function resourceDetector(string $name): ResourceDetectorInterface
    {
        $detectors = self::getFactoryInstances(ResourceDetectorFactoryInterface::class);
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
        $detectors = self::getFactoryInstances(ResourceDetectorFactoryInterface::class);

        return array_values($detectors);
    }
}
