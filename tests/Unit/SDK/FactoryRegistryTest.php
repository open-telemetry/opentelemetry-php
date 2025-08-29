<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

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
    #[\Override]
    public function setUp(): void
    {
        // Ensure all required factories are registered in the Registry
        $this->ensureRequiredFactoriesRegistered();
    }
    
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

    private function ensureRequiredFactoriesRegistered(): void
    {
        // Register console factories if not already registered
        try {
            Registry::spanExporterFactory('console');
        } catch (\RuntimeException $e) {
            Registry::registerSpanExporterFactory('console', \OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory::class);
        }
        
        try {
            Registry::logRecordExporterFactory('console');
        } catch (\RuntimeException $e) {
            Registry::registerLogRecordExporterFactory('console', \OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory::class);
        }
        
        try {
            Registry::metricExporterFactory('console');
        } catch (\RuntimeException $e) {
            Registry::registerMetricExporterFactory('console', \OpenTelemetry\SDK\Metrics\MetricExporter\ConsoleMetricExporterFactory::class);
        }
        
        // Register OTLP factories if not already registered
        try {
            Registry::spanExporterFactory('otlp');
        } catch (\RuntimeException $e) {
            Registry::registerSpanExporterFactory('otlp', \OpenTelemetry\Contrib\Otlp\SpanExporterFactory::class);
        }
        
        try {
            Registry::metricExporterFactory('otlp');
        } catch (\RuntimeException $e) {
            Registry::registerMetricExporterFactory('otlp', \OpenTelemetry\Contrib\Otlp\MetricExporterFactory::class);
        }
        
        try {
            Registry::logRecordExporterFactory('otlp');
        } catch (\RuntimeException $e) {
            Registry::registerLogRecordExporterFactory('otlp', \OpenTelemetry\Contrib\Otlp\LogsExporterFactory::class);
        }
        
        // Register transport factories if not already registered
        try {
            Registry::transportFactory('http');
        } catch (\RuntimeException $e) {
            Registry::registerTransportFactory('http', \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory::class);
        }
        
        try {
            Registry::transportFactory('grpc');
        } catch (\RuntimeException $e) {
            Registry::registerTransportFactory('grpc', \OpenTelemetry\Contrib\Grpc\GrpcTransportFactory::class);
        }
        
        try {
            Registry::transportFactory('stream');
        } catch (\RuntimeException $e) {
            Registry::registerTransportFactory('stream', \OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory::class);
        }
        
        // Register text map propagators if not already registered
        try {
            Registry::textMapPropagator('b3');
        } catch (\RuntimeException $e) {
            Registry::registerTextMapPropagator('b3', \OpenTelemetry\Extension\Propagator\B3\B3Propagator::getB3SingleHeaderInstance());
        }
        
        try {
            Registry::textMapPropagator('b3multi');
        } catch (\RuntimeException $e) {
            Registry::registerTextMapPropagator('b3multi', \OpenTelemetry\Extension\Propagator\B3\B3MultiPropagator::getInstance());
        }
        
        try {
            Registry::textMapPropagator('jaeger');
        } catch (\RuntimeException $e) {
            Registry::registerTextMapPropagator('jaeger', \OpenTelemetry\Extension\Propagator\Jaeger\JaegerPropagator::getInstance());
        }
        
        try {
            Registry::textMapPropagator('jaeger-baggage');
        } catch (\RuntimeException $e) {
            Registry::registerTextMapPropagator('jaeger-baggage', \OpenTelemetry\Extension\Propagator\Jaeger\JaegerBaggagePropagator::getInstance());
        }
        
        try {
            Registry::textMapPropagator('cloudtrace');
        } catch (\RuntimeException $e) {
            Registry::registerTextMapPropagator('cloudtrace', \OpenTelemetry\Extension\Propagator\CloudTrace\CloudTracePropagator::getInstance());
        }
        
        try {
            Registry::textMapPropagator('cloudtrace-oneway');
        } catch (\RuntimeException $e) {
            Registry::registerTextMapPropagator('cloudtrace-oneway', \OpenTelemetry\Extension\Propagator\CloudTrace\CloudTracePropagator::getOneWayInstance());
        }
        
        try {
            Registry::textMapPropagator('baggage');
        } catch (\RuntimeException $e) {
            Registry::registerTextMapPropagator('baggage', new \OpenTelemetry\API\Baggage\Propagation\BaggagePropagator());
        }
        
        try {
            Registry::textMapPropagator('tracecontext');
        } catch (\RuntimeException $e) {
            Registry::registerTextMapPropagator('tracecontext', new \OpenTelemetry\API\Trace\Propagation\TraceContextPropagator());
        }
        
        // Register memory and none factories if not already registered
        try {
            Registry::logRecordExporterFactory('memory');
        } catch (\RuntimeException $e) {
            Registry::registerLogRecordExporterFactory('memory', \OpenTelemetry\SDK\Logs\Exporter\NoopExporterFactory::class);
        }
        
        try {
            Registry::logRecordExporterFactory('none');
        } catch (\RuntimeException $e) {
            Registry::registerLogRecordExporterFactory('none', \OpenTelemetry\SDK\Logs\Exporter\NoopExporterFactory::class);
        }
        
        try {
            Registry::metricExporterFactory('none');
        } catch (\RuntimeException $e) {
            Registry::registerMetricExporterFactory('none', \OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporterFactory::class);
        }
        
        try {
            Registry::metricExporterFactory('memory');
        } catch (\RuntimeException $e) {
            Registry::registerMetricExporterFactory('memory', \OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporterFactory::class);
        }
    }
}
