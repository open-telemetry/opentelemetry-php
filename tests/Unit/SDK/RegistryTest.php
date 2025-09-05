final <?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TypeError;

class RegistryTest extends TestCase
{
    private $mockTransportFactory;
    private $mockSpanExporterFactory;
    private $mockMetricExporterFactory;
    private $mockLogRecordExporterFactory;
    private $mockTextMapPropagator;
    private $mockResourceDetector;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockTransportFactory = $this->createMock(TransportFactoryInterface::class);
        $this->mockSpanExporterFactory = $this->createMock(SpanExporterFactoryInterface::class);
        $this->mockMetricExporterFactory = $this->createMock(MetricExporterFactoryInterface::class);
        $this->mockLogRecordExporterFactory = $this->createMock(LogRecordExporterFactoryInterface::class);
        $this->mockTextMapPropagator = $this->createMock(TextMapPropagatorInterface::class);
        $this->mockResourceDetector = $this->createMock(ResourceDetectorInterface::class);

        // Reset static properties before each test
        $this->resetRegistry();
    }

    #[\Override]
    protected function tearDown(): void
    {
        // Reset static properties after each test
        $this->resetRegistry();
    }

    private function resetRegistry(): void
    {
        $reflection = new \ReflectionClass(Registry::class);
        $properties = [
            'spanExporterFactories',
            'transportFactories',
            'metricExporterFactories',
            'textMapPropagators',
            'logRecordExporterFactories',
            'resourceDetectors',
        ];

        foreach ($properties as $property) {
            $prop = $reflection->getProperty($property);
            $prop->setAccessible(true);
            $prop->setValue(null, []);
        }
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerTransportFactory
     * @covers \OpenTelemetry\SDK\Registry::transportFactory
     */
    public function test_register_transport_factory(): void
    {
        Registry::registerTransportFactory('http', $this->mockTransportFactory);

        $factory = Registry::transportFactory('http');
        // The Registry may create new instances for factories, so just test the type
        $this->assertInstanceOf(get_class($this->mockTransportFactory), $factory);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerTransportFactory
     * @covers \OpenTelemetry\SDK\Registry::transportFactory
     */
    public function test_register_transport_factory_with_string(): void
    {
        $factoryClass = get_class($this->mockTransportFactory);
        Registry::registerTransportFactory('http', $factoryClass);

        $factory = Registry::transportFactory('http');
        $this->assertInstanceOf($factoryClass, $factory);
        // Note: The returned factory is a new instance, not the same object
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerTransportFactory
     */
    public function test_register_transport_factory_throws_type_error_for_invalid_class(): void
    {
        $this->expectException(TypeError::class);
        Registry::registerTransportFactory('http', 'InvalidClass');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerTransportFactory
     * @covers \OpenTelemetry\SDK\Registry::transportFactory
     */
    public function test_register_transport_factory_with_clobber(): void
    {
        Registry::registerTransportFactory('http', $this->mockTransportFactory);

        $newFactory = $this->createMock(TransportFactoryInterface::class);
        Registry::registerTransportFactory('http', $newFactory, true);

        $factory = Registry::transportFactory('http');
        // The Registry may create new instances, so just test the type
        $this->assertInstanceOf(get_class($newFactory), $factory);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerSpanExporterFactory
     * @covers \OpenTelemetry\SDK\Registry::spanExporterFactory
     */
    public function test_register_span_exporter_factory(): void
    {
        Registry::registerSpanExporterFactory('zipkin', $this->mockSpanExporterFactory);

        $factory = Registry::spanExporterFactory('zipkin');
        $this->assertInstanceOf(get_class($this->mockSpanExporterFactory), $factory);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerSpanExporterFactory
     * @covers \OpenTelemetry\SDK\Registry::spanExporterFactory
     */
    public function test_register_span_exporter_factory_with_string(): void
    {
        $factoryClass = get_class($this->mockSpanExporterFactory);
        Registry::registerSpanExporterFactory('zipkin', $factoryClass);

        $factory = Registry::spanExporterFactory('zipkin');
        $this->assertInstanceOf($factoryClass, $factory);
        // Note: The returned factory is a new instance, not the same object
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerSpanExporterFactory
     */
    public function test_register_span_exporter_factory_throws_type_error_for_invalid_class(): void
    {
        $this->expectException(TypeError::class);
        Registry::registerSpanExporterFactory('zipkin', 'InvalidClass');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerMetricExporterFactory
     * @covers \OpenTelemetry\SDK\Registry::metricExporterFactory
     */
    public function test_register_metric_exporter_factory(): void
    {
        Registry::registerMetricExporterFactory('otlp', $this->mockMetricExporterFactory);

        $factory = Registry::metricExporterFactory('otlp');
        $this->assertInstanceOf(get_class($this->mockMetricExporterFactory), $factory);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerMetricExporterFactory
     * @covers \OpenTelemetry\SDK\Registry::metricExporterFactory
     */
    public function test_register_metric_exporter_factory_with_string(): void
    {
        $factoryClass = get_class($this->mockMetricExporterFactory);
        Registry::registerMetricExporterFactory('otlp', $factoryClass);

        $factory = Registry::metricExporterFactory('otlp');
        $this->assertInstanceOf($factoryClass, $factory);
        // Note: The returned factory is a new instance, not the same object
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerMetricExporterFactory
     */
    public function test_register_metric_exporter_factory_throws_type_error_for_invalid_class(): void
    {
        $this->expectException(TypeError::class);
        Registry::registerMetricExporterFactory('otlp', 'InvalidClass');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerLogRecordExporterFactory
     * @covers \OpenTelemetry\SDK\Registry::logRecordExporterFactory
     */
    public function test_register_log_record_exporter_factory(): void
    {
        Registry::registerLogRecordExporterFactory('otlp', $this->mockLogRecordExporterFactory);

        $factory = Registry::logRecordExporterFactory('otlp');
        $this->assertInstanceOf(get_class($this->mockLogRecordExporterFactory), $factory);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerLogRecordExporterFactory
     * @covers \OpenTelemetry\SDK\Registry::logRecordExporterFactory
     */
    public function test_register_log_record_exporter_factory_with_string(): void
    {
        $factoryClass = get_class($this->mockLogRecordExporterFactory);
        Registry::registerLogRecordExporterFactory('otlp', $factoryClass);

        $factory = Registry::logRecordExporterFactory('otlp');
        $this->assertInstanceOf($factoryClass, $factory);
        // Note: The returned factory is a new instance, not the same object
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerLogRecordExporterFactory
     */
    public function test_register_log_record_exporter_factory_throws_type_error_for_invalid_class(): void
    {
        $this->expectException(TypeError::class);
        Registry::registerLogRecordExporterFactory('otlp', 'InvalidClass');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerTextMapPropagator
     * @covers \OpenTelemetry\SDK\Registry::textMapPropagator
     */
    public function test_register_text_map_propagator(): void
    {
        Registry::registerTextMapPropagator('b3', $this->mockTextMapPropagator);

        $propagator = Registry::textMapPropagator('b3');
        $this->assertSame($this->mockTextMapPropagator, $propagator);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerTextMapPropagator
     * @covers \OpenTelemetry\SDK\Registry::textMapPropagator
     */
    public function test_register_text_map_propagator_with_clobber(): void
    {
        Registry::registerTextMapPropagator('b3', $this->mockTextMapPropagator);

        $newPropagator = $this->createMock(TextMapPropagatorInterface::class);
        Registry::registerTextMapPropagator('b3', $newPropagator, true);

        $propagator = Registry::textMapPropagator('b3');
        $this->assertSame($newPropagator, $propagator);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerResourceDetector
     * @covers \OpenTelemetry\SDK\Registry::resourceDetector
     */
    public function test_register_resource_detector(): void
    {
        Registry::registerResourceDetector('host', $this->mockResourceDetector);

        $detector = Registry::resourceDetector('host');
        $this->assertSame($this->mockResourceDetector, $detector);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::transportFactory
     */
    public function test_transport_factory_with_protocol_containing_content_type(): void
    {
        Registry::registerTransportFactory('http', $this->mockTransportFactory);

        $factory = Registry::transportFactory('http/json');
        $this->assertInstanceOf(get_class($this->mockTransportFactory), $factory);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::transportFactory
     */
    public function test_transport_factory_throws_exception_for_unknown_protocol(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Transport factory not defined for protocol: unknown');

        Registry::transportFactory('unknown');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::spanExporterFactory
     */
    public function test_span_exporter_factory_throws_exception_for_unknown_exporter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Span exporter factory not defined for: unknown');

        Registry::spanExporterFactory('unknown');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::metricExporterFactory
     */
    public function test_metric_exporter_factory_throws_exception_for_unknown_exporter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Metric exporter factory not registered for protocol: unknown');

        Registry::metricExporterFactory('unknown');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::logRecordExporterFactory
     */
    public function test_log_record_exporter_factory_throws_exception_for_unknown_exporter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('LogRecord exporter factory not defined for: unknown');

        Registry::logRecordExporterFactory('unknown');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::textMapPropagator
     */
    public function test_text_map_propagator_throws_exception_for_unknown_name(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Text map propagator not registered for: unknown');

        Registry::textMapPropagator('unknown');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::resourceDetector
     */
    public function test_resource_detector_throws_exception_for_unknown_name(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Resource detector not registered for: unknown');

        Registry::resourceDetector('unknown');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::resourceDetectors
     */
    public function test_resource_detectors_returns_array(): void
    {
        Registry::registerResourceDetector('host', $this->mockResourceDetector);

        $detectors = Registry::resourceDetectors();
        $this->assertIsArray($detectors);
        $this->assertCount(1, $detectors);
        $this->assertSame($this->mockResourceDetector, $detectors[0]);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerResourceDetector
     * @covers \OpenTelemetry\SDK\Registry::resourceDetectors
     */
    public function test_multiple_resource_detectors(): void
    {
        $detector2 = $this->createMock(ResourceDetectorInterface::class);

        Registry::registerResourceDetector('host', $this->mockResourceDetector);
        Registry::registerResourceDetector('process', $detector2);

        $detectors = Registry::resourceDetectors();
        $this->assertCount(2, $detectors);
        $this->assertContains($this->mockResourceDetector, $detectors);
        $this->assertContains($detector2, $detectors);
    }
}
