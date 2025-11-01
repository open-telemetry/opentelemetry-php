<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Instrumentation;

use OpenTelemetry\API\Instrumentation\InstrumentationInterface;
use OpenTelemetry\API\Instrumentation\InstrumentationTrait;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Propagation\NoopResponsePropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

#[CoversClass(InstrumentationTrait::class)]
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

    public function test_response_propagator(): void
    {
        $instrumentation = $this->createValidImplementation();

        $this->assertInstanceOf(NoopResponsePropagator::class, $instrumentation->getResponsePropagator());

        $responsePropagator = $this->createMock(ResponsePropagatorInterface::class);

        $instrumentation->setResponsePropagator($responsePropagator);

        $this->assertSame(
            $responsePropagator,
            $instrumentation->getResponsePropagator()
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
        return new \OpenTelemetry\Tests\Unit\API\Instrumentation\ValidInstrumentation();
    }

    private function createInvalidImplementation(): object
    {
        return new \OpenTelemetry\Tests\Unit\API\Instrumentation\InvalidInstrumentation();
    }
}

class ValidInstrumentation implements InstrumentationInterface
{
    use InstrumentationTrait;

    #[\Override]
    public function getName(): string
    {
        return InstrumentationTraitTest::INSTRUMENTATION_NAME;
    }

    #[\Override]
    public function getVersion(): ?string
    {
        return InstrumentationTraitTest::INSTRUMENTATION_VERSION;
    }

    #[\Override]
    public function getSchemaUrl(): ?string
    {
        return InstrumentationTraitTest::INSTRUMENTATION_SCHEMA_URL;
    }

    #[\Override]
    public function init(): bool
    {
        return true;
    }
}

class InvalidInstrumentation
{
    use InstrumentationTrait;

    #[\Override]
    public function getName(): string
    {
        return InstrumentationTraitTest::INSTRUMENTATION_NAME;
    }

    #[\Override]
    public function getVersion(): ?string
    {
        return InstrumentationTraitTest::INSTRUMENTATION_VERSION;
    }

    #[\Override]
    public function getSchemaUrl(): ?string
    {
        return InstrumentationTraitTest::INSTRUMENTATION_SCHEMA_URL;
    }

    #[\Override]
    public function init(): bool
    {
        return true;
    }
}
