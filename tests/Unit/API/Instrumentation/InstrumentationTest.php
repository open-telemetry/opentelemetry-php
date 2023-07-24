<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Instrumentation;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Instrumentation\ConfigurationResolverInterface;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\API\Instrumentation\NoopConfigurationResolver;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Logs\NoopLoggerProvider;
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

/**
 * @covers \OpenTelemetry\API\Globals
 * @covers \OpenTelemetry\API\Instrumentation\CachedInstrumentation
 * @covers \OpenTelemetry\API\Instrumentation\Configurator
 * @covers \OpenTelemetry\API\Instrumentation\ContextKeys
 */
final class InstrumentationTest extends TestCase
{
    public function tearDown(): void
    {
        Globals::reset();
    }

    public function test_globals_not_configured_returns_noop_instances(): void
    {
        $this->assertInstanceOf(NoopTracerProvider::class, Globals::tracerProvider());
        $this->assertInstanceOf(NoopMeterProvider::class, Globals::meterProvider());
        $this->assertInstanceOf(NoopTextMapPropagator::class, Globals::propagator());
        $this->assertInstanceOf(NoopLoggerProvider::class, Globals::loggerProvider());
        $this->assertInstanceOf(NoopConfigurationResolver::class, Globals::configurationResolver());
    }

    public function test_globals_returns_configured_instances(): void
    {
        $tracerProvider = $this->createMock(TracerProviderInterface::class);
        $meterProvider = $this->createMock(MeterProviderInterface::class);
        $propagator = $this->createMock(TextMapPropagatorInterface::class);
        $loggerProvider = $this->createMock(LoggerProviderInterface::class);
        $configurationResolver = $this->createMock(ConfigurationResolverInterface::class);

        $scope = Configurator::create()
            ->withTracerProvider($tracerProvider)
            ->withMeterProvider($meterProvider)
            ->withPropagator($propagator)
            ->withLoggerProvider($loggerProvider)
            ->withConfigurationResolver($configurationResolver)
            ->activate();

        try {
            $this->assertSame($tracerProvider, Globals::tracerProvider());
            $this->assertSame($meterProvider, Globals::meterProvider());
            $this->assertSame($propagator, Globals::propagator());
            $this->assertSame($loggerProvider, Globals::loggerProvider());
            $this->assertSame($configurationResolver, Globals::configurationResolver());
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

    public function test_initializers(): void
    {
        $called = false;
        $closure = function(Configurator $configurator) use (&$called): Configurator
        {
            $called = true;
            return $configurator;
        };
        Globals::registerInitializer($closure);
        $this->assertFalse($called);
        Globals::propagator();
        $this->assertTrue($called);
    }
}
