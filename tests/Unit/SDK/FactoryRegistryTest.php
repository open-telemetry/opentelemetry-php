<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TypeError;

#[CoversClass(Registry::class)]
class FactoryRegistryTest extends TestCase
{
    #[DataProvider('transportProtocolsProvider')]
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

    #[DataProvider('spanExporterProvider')]
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

    #[DataProvider('metricExporterProvider')]
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

    #[DataProvider('logRecordExporterProvider')]
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

    #[DataProvider('textMapPropagator')]
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

    #[DataProvider('responsePropagator')]
    public function test_default_response_propagator(string $name): void
    {
        $responsePropagator = Registry::responsePropagator($name);
        $this->assertInstanceOf(ResponsePropagatorInterface::class, $responsePropagator);
    }

    public static function responsePropagator(): array
    {
        return [
            ['none'],
        ];
    }

    #[DataProvider('invalidFactoryProvider')]
    public function test_register_invalid_transport_factory($factory): void
    {
        $this->expectException(TypeError::class);
        Registry::registerTransportFactory('http', $factory, true);
    }

    #[DataProvider('invalidFactoryProvider')]
    public function test_register_invalid_span_exporter_factory($factory): void
    {
        $this->expectException(TypeError::class);
        Registry::registerSpanExporterFactory('foo', $factory, true);
    }

    #[DataProvider('invalidFactoryProvider')]
    public function test_register_invalid_metric_exporter_factory($factory): void
    {
        $this->expectException(TypeError::class);
        Registry::registerMetricExporterFactory('foo', $factory, true);
    }

    #[DataProvider('invalidFactoryProvider')]
    public function test_register_invalid_log_record_exporter_factory($factory): void
    {
        $this->expectException(TypeError::class);
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
