<?php

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
            'resourceDetectors'
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
    public function testRegisterTransportFactory(): void
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
    public function testRegisterTransportFactoryWithString(): void
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
    public function testRegisterTransportFactoryThrowsTypeErrorForInvalidClass(): void
    {
        $this->expectException(TypeError::class);
        Registry::registerTransportFactory('http', 'InvalidClass');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerTransportFactory
     * @covers \OpenTelemetry\SDK\Registry::transportFactory
     */
    public function testRegisterTransportFactoryWithClobber(): void
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
    public function testRegisterSpanExporterFactory(): void
    {
        Registry::registerSpanExporterFactory('zipkin', $this->mockSpanExporterFactory);
        
        $factory = Registry::spanExporterFactory('zipkin');
        $this->assertInstanceOf(get_class($this->mockSpanExporterFactory), $factory);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerSpanExporterFactory
     * @covers \OpenTelemetry\SDK\Registry::spanExporterFactory
     */
    public function testRegisterSpanExporterFactoryWithString(): void
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
    public function testRegisterSpanExporterFactoryThrowsTypeErrorForInvalidClass(): void
    {
        $this->expectException(TypeError::class);
        Registry::registerSpanExporterFactory('zipkin', 'InvalidClass');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerMetricExporterFactory
     * @covers \OpenTelemetry\SDK\Registry::metricExporterFactory
     */
    public function testRegisterMetricExporterFactory(): void
    {
        Registry::registerMetricExporterFactory('otlp', $this->mockMetricExporterFactory);
        
        $factory = Registry::metricExporterFactory('otlp');
        $this->assertInstanceOf(get_class($this->mockMetricExporterFactory), $factory);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerMetricExporterFactory
     * @covers \OpenTelemetry\SDK\Registry::metricExporterFactory
     */
    public function testRegisterMetricExporterFactoryWithString(): void
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
    public function testRegisterMetricExporterFactoryThrowsTypeErrorForInvalidClass(): void
    {
        $this->expectException(TypeError::class);
        Registry::registerMetricExporterFactory('otlp', 'InvalidClass');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerLogRecordExporterFactory
     * @covers \OpenTelemetry\SDK\Registry::logRecordExporterFactory
     */
    public function testRegisterLogRecordExporterFactory(): void
    {
        Registry::registerLogRecordExporterFactory('otlp', $this->mockLogRecordExporterFactory);
        
        $factory = Registry::logRecordExporterFactory('otlp');
        $this->assertInstanceOf(get_class($this->mockLogRecordExporterFactory), $factory);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerLogRecordExporterFactory
     * @covers \OpenTelemetry\SDK\Registry::logRecordExporterFactory
     */
    public function testRegisterLogRecordExporterFactoryWithString(): void
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
    public function testRegisterLogRecordExporterFactoryThrowsTypeErrorForInvalidClass(): void
    {
        $this->expectException(TypeError::class);
        Registry::registerLogRecordExporterFactory('otlp', 'InvalidClass');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerTextMapPropagator
     * @covers \OpenTelemetry\SDK\Registry::textMapPropagator
     */
    public function testRegisterTextMapPropagator(): void
    {
        Registry::registerTextMapPropagator('b3', $this->mockTextMapPropagator);
        
        $propagator = Registry::textMapPropagator('b3');
        $this->assertSame($this->mockTextMapPropagator, $propagator);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::registerTextMapPropagator
     * @covers \OpenTelemetry\SDK\Registry::textMapPropagator
     */
    public function testRegisterTextMapPropagatorWithClobber(): void
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
    public function testRegisterResourceDetector(): void
    {
        Registry::registerResourceDetector('host', $this->mockResourceDetector);
        
        $detector = Registry::resourceDetector('host');
        $this->assertSame($this->mockResourceDetector, $detector);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::transportFactory
     */
    public function testTransportFactoryWithProtocolContainingContentType(): void
    {
        Registry::registerTransportFactory('http', $this->mockTransportFactory);
        
        $factory = Registry::transportFactory('http/json');
        $this->assertInstanceOf(get_class($this->mockTransportFactory), $factory);
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::transportFactory
     */
    public function testTransportFactoryThrowsExceptionForUnknownProtocol(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Transport factory not defined for protocol: unknown');
        
        Registry::transportFactory('unknown');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::spanExporterFactory
     */
    public function testSpanExporterFactoryThrowsExceptionForUnknownExporter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Span exporter factory not defined for: unknown');
        
        Registry::spanExporterFactory('unknown');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::metricExporterFactory
     */
    public function testMetricExporterFactoryThrowsExceptionForUnknownExporter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Metric exporter factory not registered for protocol: unknown');
        
        Registry::metricExporterFactory('unknown');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::logRecordExporterFactory
     */
    public function testLogRecordExporterFactoryThrowsExceptionForUnknownExporter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('LogRecord exporter factory not defined for: unknown');
        
        Registry::logRecordExporterFactory('unknown');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::textMapPropagator
     */
    public function testTextMapPropagatorThrowsExceptionForUnknownName(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Text map propagator not registered for: unknown');
        
        Registry::textMapPropagator('unknown');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::resourceDetector
     */
    public function testResourceDetectorThrowsExceptionForUnknownName(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Resource detector not registered for: unknown');
        
        Registry::resourceDetector('unknown');
    }

    /**
     * @covers \OpenTelemetry\SDK\Registry::resourceDetectors
     */
    public function testResourceDetectorsReturnsArray(): void
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
    public function testMultipleResourceDetectors(): void
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
