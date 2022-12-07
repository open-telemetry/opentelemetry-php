<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Instrumentation;

use OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Common\Instrumentation\Configurator;
use OpenTelemetry\API\Common\Instrumentation\Globals;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @covers \OpenTelemetry\API\Common\Instrumentation\Globals
 * @covers \OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation
 * @covers \OpenTelemetry\API\Common\Instrumentation\Configurator
 * @covers \OpenTelemetry\API\Common\Instrumentation\ContextKeys
 */
final class InstrumentationTest extends TestCase
{
    public function test_globals_not_configured_returns_noop_instances(): void
    {
        $this->assertInstanceOf(NoopTracerProvider::class, Globals::tracerProvider());
        $this->assertInstanceOf(NoopMeterProvider::class, Globals::meterProvider());
        $this->assertInstanceOf(NoopTextMapPropagator::class, Globals::propagator());
        $this->assertInstanceOf(NullLogger::class, Globals::logger());
    }

    public function test_globals_returns_configured_instances(): void
    {
        $tracerProvider = $this->createMock(TracerProviderInterface::class);
        $meterProvider = $this->createMock(MeterProviderInterface::class);
        $propagator = $this->createMock(TextMapPropagatorInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $scope = Configurator::create()
            ->withTracerProvider($tracerProvider)
            ->withMeterProvider($meterProvider)
            ->withPropagator($propagator)
            ->withLogger($logger)
            ->activate();

        try {
            $this->assertSame($tracerProvider, Globals::tracerProvider());
            $this->assertSame($meterProvider, Globals::meterProvider());
            $this->assertSame($propagator, Globals::propagator());
            $this->assertSame($logger, Globals::logger());
        } finally {
            $scope->detach();
        }
    }

    public function test_instrumentation_not_configured_returns_noop_instances(): void
    {
        $instrumentation = new CachedInstrumentation('', null, null, []);

        $this->assertInstanceOf(NoopTracer::class, $instrumentation->tracer());
        $this->assertInstanceOf(NoopMeter::class, $instrumentation->meter());
    }

    public function test_instrumentation_returns_configured_instances(): void
    {
        $instrumentation = new CachedInstrumentation('', null, null, []);

        $tracer = $this->createMock(TracerInterface::class);
        $tracerProvider = $this->createMock(TracerProviderInterface::class);
        $tracerProvider->method('getTracer')->willReturn($tracer);
        $meter = $this->createMock(MeterInterface::class);
        $meterProvider = $this->createMock(MeterProviderInterface::class);
        $meterProvider->method('getMeter')->willReturn($meter);
        $propagator = $this->createMock(TextMapPropagatorInterface::class);

        $scope = Configurator::create()
            ->withTracerProvider($tracerProvider)
            ->withMeterProvider($meterProvider)
            ->withPropagator($propagator)
            ->activate();

        try {
            $this->assertSame($tracer, $instrumentation->tracer());
            $this->assertSame($meter, $instrumentation->meter());
        } finally {
            $scope->detach();
        }
    }
}
