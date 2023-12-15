<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use PHPUnit\Framework\Exception as PHPUnitFrameworkException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Registry
 */
class FactoryRegistryTest extends TestCase
{
    /**
     * @dataProvider transportProtocolsProvider
     */
    public function test_default_transport_factories(string $name): void
    {
        $factory = Registry::transportFactory($name);
        $this->assertInstanceOf(TransportFactoryInterface::class, $factory);
    }

    public static function transportProtocolsProvider(): array
    {
        return [
            ['grpc'],
            ['http/protobuf'],
            ['http/json'],
            ['http/ndjson'],
            ['http'],
            ['http/foo'],
        ];
    }

    /**
     * @dataProvider spanExporterProvider
     */
    public function test_default_span_exporter_factories(string $name): void
    {
        $factory = Registry::spanExporterFactory($name);
        $this->assertInstanceOf(SpanExporterFactoryInterface::class, $factory);
    }

    public static function spanExporterProvider(): array
    {
        return [
            ['otlp'],
            ['zipkin'],
            ['console'],
            ['memory'],
        ];
    }

    /**
     * @dataProvider metricExporterProvider
     */
    public function test_default_metric_exporter_factories(string $name): void
    {
        $factory = Registry::metricExporterFactory($name);
        $this->assertInstanceOf(MetricExporterFactoryInterface::class, $factory);
    }

    public static function metricExporterProvider(): array
    {
        return [
            ['otlp'],
            ['memory'],
            ['none'],
        ];
    }

    /**
     * @dataProvider logRecordExporterProvider
     */
    public function test_default_log_record_exporter_factories(string $name): void
    {
        $factory = Registry::logRecordExporterFactory($name);
        $this->assertInstanceOf(LogRecordExporterFactoryInterface::class, $factory);
    }

    public static function logRecordExporterProvider(): array
    {
        return [
            ['console'],
            ['memory'],
        ];
    }

    /**
     * @dataProvider textMapPropagator
     */
    public function test_default_text_map_propagator(string $name): void
    {
        $propagator = Registry::textMapPropagator($name);
        $this->assertInstanceOf(TextMapPropagatorInterface::class, $propagator);
    }

    public static function textMapPropagator(): array
    {
        return [
            ['tracecontext'],
            ['baggage'],
            ['b3'],
            ['b3multi'],
            ['cloudtrace'],
            ['cloudtrace-oneway'],
            ['jaeger'],
            ['jaeger-baggage'],
        ];
    }

    /**
     * @dataProvider invalidFactoryProvider
     */
    public function test_register_invalid_transport_factory($factory): void
    {
        $this->expectException(PHPUnitFrameworkException::class);
        Registry::registerTransportFactory('http', $factory, true);
    }

    /**
     * @dataProvider invalidFactoryProvider
     */
    public function test_register_invalid_span_exporter_factory($factory): void
    {
        $this->expectException(PHPUnitFrameworkException::class);
        Registry::registerSpanExporterFactory('foo', $factory, true);
    }

    /**
     * @dataProvider invalidFactoryProvider
     */
    public function test_register_invalid_metric_exporter_factory($factory): void
    {
        $this->expectException(PHPUnitFrameworkException::class);
        Registry::registerMetricExporterFactory('foo', $factory, true);
    }

    /**
     * @dataProvider invalidFactoryProvider
     */
    public function test_register_invalid_log_record_exporter_factory($factory): void
    {
        $this->expectException(PHPUnitFrameworkException::class);
        Registry::registerLogRecordExporterFactory('foo', $factory, true);
    }

    public static function invalidFactoryProvider(): array
    {
        return [
            [new \stdClass()],
            ['\stdClass'],
        ];
    }
}
