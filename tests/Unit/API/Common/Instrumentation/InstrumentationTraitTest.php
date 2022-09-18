<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Instrumentation;

use OpenTelemetry\API\Common\Instrumentation\InstrumentationInterface;
use OpenTelemetry\API\Common\Instrumentation\InstrumentationTrait;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

/**
 * @covers \OpenTelemetry\API\Common\Instrumentation\InstrumentationTrait
 */
class InstrumentationTraitTest extends TestCase
{
    public const INSTRUMENTATION_NAME = 'test-instrumentation';
    public const INSTRUMENTATION_VERSION = '1.2.3';
    public const INSTRUMENTATION_SCHEMA_URL = null;

    public function test_propagator(): void
    {
        $instrumentation = $this->createValidImplementation();

        $this->assertInstanceOf(NoopTextMapPropagator::class, $instrumentation->getPropagator());

        $propagator = $this->createMock(TextMapPropagatorInterface::class);

        $instrumentation->setPropagator($propagator);

        $this->assertSame(
            $propagator,
            $instrumentation->getPropagator()
        );
    }

    public function test_tracer(): void
    {
        $instrumentation = $this->createValidImplementation();

        $this->assertInstanceOf(NoopTracer::class, $instrumentation->getTracer());

        $tracer = $this->createMock(TracerInterface::class);
        $tracerProvider = $this->createMock(TracerProviderInterface::class);
        $tracerProvider->expects($this->once())
            ->method('getTracer')
            ->willReturn($tracer);

        $instrumentation->setTracerProvider($tracerProvider);

        $this->assertSame(
            $tracer,
            $instrumentation->getTracer()
        );
    }

    public function test_meter(): void
    {
        $instrumentation = $this->createValidImplementation();

        $this->assertInstanceOf(NoopMeter::class, $instrumentation->getMeter());

        $meter = $this->createMock(MeterInterface::class);
        $meterProvider = $this->createMock(MeterProviderInterface::class);
        $meterProvider->expects($this->once())
            ->method('getMeter')
            ->willReturn($meter);

        $instrumentation->setMeterProvider($meterProvider);

        $this->assertSame(
            $meter,
            $instrumentation->getMeter()
        );
    }

    public function test_logger(): void
    {
        $instrumentation = $this->createValidImplementation();

        $this->assertInstanceOf(NullLogger::class, $instrumentation->getLogger());

        $logger = $this->createMock(LoggerInterface::class);

        $instrumentation->setLogger($logger);

        $this->assertSame(
            $logger,
            $instrumentation->getLogger()
        );
    }

    public function test_activate(): void
    {
        $this->assertTrue(
            $this->createValidImplementation()->activate()
        );
    }

    public function test_activate_throws_exception_on_non_instrumentation_interface(): void
    {
        $this->expectException(RuntimeException::class);

        $this->createInvalidImplementation()->activate();
    }

    private function createValidImplementation(): InstrumentationInterface
    {
        return new ValidInstrumentation();
    }

    private function createInvalidImplementation(): object
    {
        return new InvalidInstrumentation();
    }
}

class ValidInstrumentation implements InstrumentationInterface
{
    use InstrumentationTrait;

    public function getName(): string
    {
        return InstrumentationTraitTest::INSTRUMENTATION_NAME;
    }

    public function getVersion(): ?string
    {
        return InstrumentationTraitTest::INSTRUMENTATION_VERSION;
    }

    public function getSchemaUrl(): ?string
    {
        return InstrumentationTraitTest::INSTRUMENTATION_SCHEMA_URL;
    }

    public function init(): bool
    {
        return true;
    }
}

class InvalidInstrumentation
{
    use InstrumentationTrait;

    public function getName(): string
    {
        return InstrumentationTraitTest::INSTRUMENTATION_NAME;
    }

    public function getVersion(): ?string
    {
        return InstrumentationTraitTest::INSTRUMENTATION_VERSION;
    }

    public function getSchemaUrl(): ?string
    {
        return InstrumentationTraitTest::INSTRUMENTATION_SCHEMA_URL;
    }

    public function init(): bool
    {
        return true;
    }
}
