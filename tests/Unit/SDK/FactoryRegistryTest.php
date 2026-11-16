<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\API\Baggage\Propagation\BaggagePropagator;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporterFactory;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporter\ConsoleMetricExporterFactory;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporterFactory as InMemoryMetricExporterFactory;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemorySpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TypeError;

#[CoversClass(Registry::class)]
class FactoryRegistryTest extends TestCase
{
    public function test_transport_factory_names_are_case_insensitive(): void
    {
        Registry::registerTransportFactory('Acme', StreamTransportFactory::class, true);
        Registry::registerTransportFactory('acme', OtlpHttpTransportFactory::class);

        $this->assertSame(StreamTransportFactory::class, Registry::transportFactory('acme')::class);
        $this->assertSame(StreamTransportFactory::class, Registry::transportFactory('Acme')::class);
        $this->assertSame(StreamTransportFactory::class, Registry::transportFactory('ACME/protobuf')::class);

        Registry::registerTransportFactory('acme', OtlpHttpTransportFactory::class, true);
        $this->assertSame(OtlpHttpTransportFactory::class, Registry::transportFactory('Acme')::class);
    }

    public function test_span_exporter_factory_names_are_case_insensitive(): void
    {
        Registry::registerSpanExporterFactory('Acme', ConsoleSpanExporterFactory::class, true);
        Registry::registerSpanExporterFactory('acme', InMemorySpanExporterFactory::class);

        $this->assertSame(ConsoleSpanExporterFactory::class, Registry::spanExporterFactory('acme')::class);
        $this->assertSame(ConsoleSpanExporterFactory::class, Registry::spanExporterFactory('Acme')::class);

        Registry::registerSpanExporterFactory('acme', InMemorySpanExporterFactory::class, true);
        $this->assertSame(InMemorySpanExporterFactory::class, Registry::spanExporterFactory('Acme')::class);
    }

    public function test_metric_exporter_factory_names_are_case_insensitive(): void
    {
        Registry::registerMetricExporterFactory('Acme', ConsoleMetricExporterFactory::class, true);
        Registry::registerMetricExporterFactory('acme', InMemoryMetricExporterFactory::class);

        $this->assertSame(ConsoleMetricExporterFactory::class, Registry::metricExporterFactory('acme')::class);
        $this->assertSame(ConsoleMetricExporterFactory::class, Registry::metricExporterFactory('Acme')::class);

        Registry::registerMetricExporterFactory('acme', InMemoryMetricExporterFactory::class, true);
        $this->assertSame(InMemoryMetricExporterFactory::class, Registry::metricExporterFactory('Acme')::class);
    }

    public function test_log_record_exporter_factory_names_are_case_insensitive(): void
    {
        Registry::registerLogRecordExporterFactory('Acme', ConsoleExporterFactory::class, true);
        Registry::registerLogRecordExporterFactory('acme', InMemoryExporterFactory::class);

        $this->assertSame(ConsoleExporterFactory::class, Registry::logRecordExporterFactory('acme')::class);
        $this->assertSame(ConsoleExporterFactory::class, Registry::logRecordExporterFactory('Acme')::class);

        Registry::registerLogRecordExporterFactory('acme', InMemoryExporterFactory::class, true);
        $this->assertSame(InMemoryExporterFactory::class, Registry::logRecordExporterFactory('Acme')::class);
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/configuration/sdk-environment-variables.md#enum
     */
    public function test_text_map_propagator_names_are_case_insensitive(): void
    {
        $propagator = BaggagePropagator::getInstance();
        $replacement = TraceContextPropagator::getInstance();
        Registry::registerTextMapPropagator('Acme', $propagator, true);
        Registry::registerTextMapPropagator('acme', $replacement);

        $this->assertSame($propagator, Registry::textMapPropagator('acme'));
        $this->assertSame($propagator, Registry::textMapPropagator('Acme'));

        Registry::registerTextMapPropagator('acme', $replacement, true);
        $this->assertSame($replacement, Registry::textMapPropagator('Acme'));
    }

    #[DataProvider('unregisteredComponentProvider')]
    public function test_unregistered_component_message_preserves_case(string $lookup): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Console2');

        Registry::$lookup('Console2');
    }

    public static function unregisteredComponentProvider(): array
    {
        return [
            ['spanExporterFactory'],
            ['metricExporterFactory'],
            ['logRecordExporterFactory'],
            ['textMapPropagator'],
        ];
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
