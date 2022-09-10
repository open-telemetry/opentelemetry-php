<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Instrumentation;

use OpenTelemetry\API\Common\Instrumentation\Instrumentation;
use OpenTelemetry\API\Common\Instrumentation\InstrumentationConfigurator;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\ContextStorage;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @covers \OpenTelemetry\API\Common\Instrumentation\Instrumentation
 * @covers \OpenTelemetry\API\Common\Instrumentation\InstrumentationConfigurator
 * @covers \OpenTelemetry\API\Common\Instrumentation\ContextKeys
 */
final class InstrumentationTest extends TestCase
{
    public function test_not_configured_returns_noop_instances(): void
    {
        $contextStorage = new ContextStorage();
        $instrumentation = new Instrumentation('', null, null, [], $contextStorage);

        $this->assertInstanceOf(NoopTracer::class, $instrumentation->tracer());
        $this->assertInstanceOf(NoopMeter::class, $instrumentation->meter());
        $this->assertInstanceOf(NullLogger::class, $instrumentation->logger());
        $this->assertInstanceOf(NoopTextMapPropagator::class, $instrumentation->propagator());
    }

    public function test_returns_configured_instances(): void
    {
        $contextStorage = new ContextStorage();
        $instrumentation = new Instrumentation('', null, null, [], $contextStorage);

        $tracer = $this->createMock(TracerInterface::class);
        $tracerProvider = $this->createMock(TracerProviderInterface::class);
        $tracerProvider->method('getTracer')->willReturn($tracer);
        $meter = $this->createMock(MeterInterface::class);
        $meterProvider = $this->createMock(MeterProviderInterface::class);
        $meterProvider->method('getMeter')->willReturn($meter);
        $logger = $this->createMock(LoggerInterface::class);
        $propagator = $this->createMock(TextMapPropagatorInterface::class);

        InstrumentationConfigurator::create($contextStorage)
            ->withTracerProvider($tracerProvider)
            ->withMeterProvider($meterProvider)
            ->withLogger($logger)
            ->withPropagator($propagator)
            ->activate();

        $this->assertSame($tracer, $instrumentation->tracer());
        $this->assertSame($meter, $instrumentation->meter());
        $this->assertSame($logger, $instrumentation->logger());
        $this->assertSame($propagator, $instrumentation->propagator());
    }
}
