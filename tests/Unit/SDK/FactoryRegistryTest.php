<?php

declare(strict_types=1);

namespace OpenTelemetry\Example\Unit\SDK;

use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\FactoryRegistry;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\FactoryRegistry
 */
class FactoryRegistryTest extends TestCase
{
    /**
     * @dataProvider transportProtocolsProvider
     */
    public function test_default_transport_factories(string $name): void
    {
        $factory = FactoryRegistry::transportFactory($name);
        $this->assertInstanceOf(TransportFactoryInterface::class, $factory);
    }

    public function transportProtocolsProvider(): array
    {
        return [
            ['grpc'],
            ['http/protobuf'],
            ['http/json'],
            ['http/ndjson'],
        ];
    }

    /**
     * @dataProvider spanExporterProvider
     */
    public function test_default_span_exporter_factories(string $name): void
    {
        $factory = FactoryRegistry::spanExporterFactory($name);
        $this->assertInstanceOf(SpanExporterFactoryInterface::class, $factory);
    }

    public function spanExporterProvider(): array
    {
        return [
            ['otlp'],
            ['zipkin'],
            ['newrelic'],
            ['console'],
            ['memory'],
        ];
    }

    /**
     * @dataProvider metricExporterProvider
     */
    public function test_default_metric_exporter_factories(string $name): void
    {
        $factory = FactoryRegistry::metricExporterFactory($name);
        $this->assertInstanceOf(MetricExporterFactoryInterface::class, $factory);
    }

    public function metricExporterProvider(): array
    {
        return [
            ['otlp'],
            ['memory'],
            ['none'],
        ];
    }
}
