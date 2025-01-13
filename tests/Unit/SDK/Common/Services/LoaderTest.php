<?php

declare(strict_types=1);

namespace Unit\SDK\Common\Services;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Services\Loader;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Resource\ResourceDetectorFactoryInterface;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Loader::class)]
class LoaderTest extends TestCase
{
    use TestState;

    #[DataProvider('transportProtocolsProvider')]
    public function test_default_transport_factories(string $name): void
    {
        $factory = Loader::transportFactory($name);
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
        $factory = Loader::spanExporterFactory($name);
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
        $factory = Loader::metricExporterFactory($name);
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
        $factory = Loader::logRecordExporterFactory($name);
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
        $propagator = Loader::textMapPropagator($name);
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

    public function test_retrieve_from_spi(): void
    {
        $this->assertFileExists(dirname(__DIR__, 5) . '/vendor/composer/GeneratedServiceProviderData.php');
        $this->assertInstanceOf(ResourceDetectorInterface::class, Loader::resourceDetector('test'));
    }

    public function test_add_to_spi(): void
    {
        $factory = new class() implements ResourceDetectorFactoryInterface {
            public function create(): ResourceDetectorInterface
            {
                return new class() implements ResourceDetectorInterface {
                    public function getResource(): ResourceInfo
                    {
                        return ResourceInfo::create(Attributes::create(['foo-resource' => 'foo']));
                    }
                };
            }
            public function type(): string
            {
                return 'foo';
            }
            public function priority(): int
            {
                return 99;
            }
        };
        ServiceLoader::register(ResourceDetectorFactoryInterface::class, $factory::class);

        $detector = Loader::resourceDetector('foo');
        $this->assertInstanceOf(ResourceDetectorInterface::class, $detector);
        $this->assertTrue($detector->getResource()->getAttributes()->has('foo-resource'));
        $this->assertSame('foo', $detector->getResource()->getAttributes()->get('foo-resource'));
    }

    public function test_get_all_resource_detectors(): void
    {
        $detectors = Loader::resourceDetectors();
        $this->assertNotEmpty($detectors);
    }

    public function test_missing_propagator(): void
    {
        $this->expectException(\RuntimeException::class);
        Loader::textMapPropagator('missing');
    }

    public function test_missing_detector(): void
    {
        $this->expectException(\RuntimeException::class);
        Loader::resourceDetector('missing');
    }
}
