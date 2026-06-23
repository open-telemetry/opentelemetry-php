<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use TypeError;

#[CoversClass(Registry::class)]
class RegistryTest extends TestCase
{
    private static array $originalState = [];

    public static function setUpBeforeClass(): void
    {
        $reflection = new ReflectionClass(Registry::class);
        $properties = [
            'spanExporterFactories',
            'transportFactories',
            'metricExporterFactories',
            'textMapPropagators',
            'logRecordExporterFactories',
            'resourceDetectors',
            'responsePropagators',
        ];
        foreach ($properties as $prop) {
            $rp = $reflection->getProperty($prop);
            self::$originalState[$prop] = $rp->getValue();
        }
    }

    protected function setUp(): void
    {
        $reflection = new ReflectionClass(Registry::class);
        foreach (self::$originalState as $prop => $value) {
            $rp = $reflection->getProperty($prop);
            $rp->setValue(null, []);
        }
    }

    public static function tearDownAfterClass(): void
    {
        $reflection = new ReflectionClass(Registry::class);
        foreach (self::$originalState as $prop => $value) {
            $rp = $reflection->getProperty($prop);
            $rp->setValue(null, $value);
        }
    }

    public function test_register_transport_factory_with_object(): void
    {
        $factory = $this->createMock(TransportFactoryInterface::class);
        Registry::registerTransportFactory('http', $factory, true);
        $this->assertInstanceOf(TransportFactoryInterface::class, Registry::transportFactory('http'));
    }

    public function test_register_transport_factory_clobber_false_skips_existing(): void
    {
        $first = $this->createMock(TransportFactoryInterface::class);
        $second = $this->createMock(TransportFactoryInterface::class);
        Registry::registerTransportFactory('http', $first, true);
        Registry::registerTransportFactory('http', $second, false);
        $result = Registry::transportFactory('http');
        $this->assertNotSame($second, $result);
        $this->assertInstanceOf(TransportFactoryInterface::class, $result);
    }

    public function test_register_transport_factory_type_error_for_invalid_class(): void
    {
        $this->expectException(TypeError::class);
        Registry::registerTransportFactory('http', \stdClass::class, true);
    }

    public function test_register_span_exporter_factory_with_object(): void
    {
        $factory = $this->createMock(SpanExporterFactoryInterface::class);
        Registry::registerSpanExporterFactory('test', $factory, true);
        $this->assertInstanceOf(SpanExporterFactoryInterface::class, Registry::spanExporterFactory('test'));
    }

    public function test_register_span_exporter_factory_type_error(): void
    {
        $this->expectException(TypeError::class);
        Registry::registerSpanExporterFactory('test', \stdClass::class, true);
    }

    public function test_register_metric_exporter_factory_with_object(): void
    {
        $factory = $this->createMock(MetricExporterFactoryInterface::class);
        Registry::registerMetricExporterFactory('test', $factory, true);
        $this->assertInstanceOf(MetricExporterFactoryInterface::class, Registry::metricExporterFactory('test'));
    }

    public function test_register_metric_exporter_factory_type_error(): void
    {
        $this->expectException(TypeError::class);
        Registry::registerMetricExporterFactory('test', \stdClass::class, true);
    }

    public function test_register_log_record_exporter_factory_with_object(): void
    {
        $factory = $this->createMock(LogRecordExporterFactoryInterface::class);
        Registry::registerLogRecordExporterFactory('test', $factory, true);
        $this->assertInstanceOf(LogRecordExporterFactoryInterface::class, Registry::logRecordExporterFactory('test'));
    }

    public function test_register_log_record_exporter_factory_type_error(): void
    {
        $this->expectException(TypeError::class);
        Registry::registerLogRecordExporterFactory('test', \stdClass::class, true);
    }

    public function test_register_and_retrieve_text_map_propagator(): void
    {
        $propagator = $this->createMock(TextMapPropagatorInterface::class);
        Registry::registerTextMapPropagator('test', $propagator);
        $this->assertSame($propagator, Registry::textMapPropagator('test'));
    }

    public function test_register_and_retrieve_resource_detector(): void
    {
        $detector = $this->createMock(ResourceDetectorInterface::class);
        Registry::registerResourceDetector('test', $detector);
        $this->assertSame($detector, Registry::resourceDetector('test'));
    }

    public function test_register_and_retrieve_response_propagator(): void
    {
        $propagator = $this->createMock(ResponsePropagatorInterface::class);
        Registry::registerResponsePropagator('test', $propagator);
        $this->assertSame($propagator, Registry::responsePropagator('test'));
    }

    public function test_span_exporter_factory_throws_runtime_exception_for_missing(): void
    {
        $this->expectException(RuntimeException::class);
        Registry::spanExporterFactory('nonexistent');
    }

    public function test_log_record_exporter_factory_throws_runtime_exception_for_missing(): void
    {
        $this->expectException(RuntimeException::class);
        Registry::logRecordExporterFactory('nonexistent');
    }

    public function test_transport_factory_parses_protocol_with_content_type(): void
    {
        $factory = $this->createMock(TransportFactoryInterface::class);
        Registry::registerTransportFactory('http', $factory, true);
        $this->assertInstanceOf(TransportFactoryInterface::class, Registry::transportFactory('http/json'));
    }

    public function test_transport_factory_throws_runtime_exception_for_missing(): void
    {
        $this->expectException(RuntimeException::class);
        Registry::transportFactory('nonexistent');
    }

    public function test_metric_exporter_factory_throws_runtime_exception_for_missing(): void
    {
        $this->expectException(RuntimeException::class);
        Registry::metricExporterFactory('nonexistent');
    }

    public function test_text_map_propagator_throws_runtime_exception_for_missing(): void
    {
        $this->expectException(RuntimeException::class);
        Registry::textMapPropagator('nonexistent');
    }

    public function test_resource_detector_throws_runtime_exception_for_missing(): void
    {
        $this->expectException(RuntimeException::class);
        Registry::resourceDetector('nonexistent');
    }

    public function test_response_propagator_throws_runtime_exception_for_missing(): void
    {
        $this->expectException(RuntimeException::class);
        Registry::responsePropagator('nonexistent');
    }

    public function test_resource_detectors_returns_array(): void
    {
        $detector1 = $this->createMock(ResourceDetectorInterface::class);
        $detector2 = $this->createMock(ResourceDetectorInterface::class);
        Registry::registerResourceDetector('one', $detector1);
        Registry::registerResourceDetector('two', $detector2);
        $detectors = Registry::resourceDetectors();
        $this->assertIsArray($detectors);
        $this->assertCount(2, $detectors);
        $this->assertSame($detector1, $detectors[0]);
        $this->assertSame($detector2, $detectors[1]);
    }

    public function test_register_span_exporter_factory_clobber_false_skips_existing(): void
    {
        $first = $this->createMock(SpanExporterFactoryInterface::class);
        $second = $this->createMock(SpanExporterFactoryInterface::class);
        Registry::registerSpanExporterFactory('test-clobber', $first, true);
        Registry::registerSpanExporterFactory('test-clobber', $second, false);
        $result = Registry::spanExporterFactory('test-clobber');
        $this->assertNotSame($second, $result);
        $this->assertInstanceOf(SpanExporterFactoryInterface::class, $result);
    }

    public function test_register_metric_exporter_factory_clobber_false_skips_existing(): void
    {
        $first = $this->createMock(MetricExporterFactoryInterface::class);
        $second = $this->createMock(MetricExporterFactoryInterface::class);
        Registry::registerMetricExporterFactory('test-clobber', $first, true);
        Registry::registerMetricExporterFactory('test-clobber', $second, false);
        $result = Registry::metricExporterFactory('test-clobber');
        $this->assertNotSame($second, $result);
        $this->assertInstanceOf(MetricExporterFactoryInterface::class, $result);
    }

    public function test_register_log_record_exporter_factory_clobber_false_skips_existing(): void
    {
        $first = $this->createMock(LogRecordExporterFactoryInterface::class);
        $second = $this->createMock(LogRecordExporterFactoryInterface::class);
        Registry::registerLogRecordExporterFactory('test-clobber', $first, true);
        Registry::registerLogRecordExporterFactory('test-clobber', $second, false);
        $result = Registry::logRecordExporterFactory('test-clobber');
        $this->assertNotSame($second, $result);
        $this->assertInstanceOf(LogRecordExporterFactoryInterface::class, $result);
    }

    public function test_register_text_map_propagator_clobber_false_skips_existing(): void
    {
        $first = $this->createMock(TextMapPropagatorInterface::class);
        $second = $this->createMock(TextMapPropagatorInterface::class);
        Registry::registerTextMapPropagator('test', $first, true);
        Registry::registerTextMapPropagator('test', $second, false);
        $this->assertSame($first, Registry::textMapPropagator('test'));
    }

    public function test_register_response_propagator_clobber_false_skips_existing(): void
    {
        $first = $this->createMock(ResponsePropagatorInterface::class);
        $second = $this->createMock(ResponsePropagatorInterface::class);
        Registry::registerResponsePropagator('test', $first, true);
        Registry::registerResponsePropagator('test', $second, false);
        $this->assertSame($first, Registry::responsePropagator('test'));
    }
}
