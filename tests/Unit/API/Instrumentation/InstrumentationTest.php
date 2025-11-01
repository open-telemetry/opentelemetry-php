<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Instrumentation;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\API\Instrumentation\ContextKeys;
use OpenTelemetry\API\Logs\EventLoggerInterface;
use OpenTelemetry\API\Logs\EventLoggerProviderInterface;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Logs\NoopEventLoggerProvider;
use OpenTelemetry\API\Logs\NoopLoggerProvider;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Propagation\NoopResponsePropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

#[CoversClass(Globals::class)]
#[CoversClass(CachedInstrumentation::class)]
#[CoversClass(Configurator::class)]
#[CoversClass(ContextKeys::class)]
final class InstrumentationTest extends TestCase
{
    private LogWriterInterface&MockObject $logWriter;

    #[\Override]
    public function setUp(): void
    {
        $this->logWriter = $this->createMock(LogWriterInterface::class);
        Logging::setLogWriter($this->logWriter);
    }

    #[\Override]
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
        $this->assertInstanceOf(NoopEventLoggerProvider::class, Globals::eventLoggerProvider());
        $this->assertInstanceOf(NoopResponsePropagator::class, Globals::responsePropagator());
    }

    public function test_globals_returns_configured_instances(): void
    {
        $tracerProvider = $this->createMock(TracerProviderInterface::class);
        $meterProvider = $this->createMock(MeterProviderInterface::class);
        $propagator = $this->createMock(TextMapPropagatorInterface::class);
        $loggerProvider = $this->createMock(LoggerProviderInterface::class);
        $eventLoggerProvider = $this->createMock(EventLoggerProviderInterface::class);
        $responsePropagator = $this->createMock(ResponsePropagatorInterface::class);

        $scope = Configurator::create()
            ->withTracerProvider($tracerProvider)
            ->withMeterProvider($meterProvider)
            ->withPropagator($propagator)
            ->withLoggerProvider($loggerProvider)
            ->withEventLoggerProvider($eventLoggerProvider)
            ->withResponsePropagator($responsePropagator)
            ->activate();

        try {
            $this->assertSame($tracerProvider, Globals::tracerProvider());
            $this->assertSame($meterProvider, Globals::meterProvider());
            $this->assertSame($propagator, Globals::propagator());
            $this->assertSame($loggerProvider, Globals::loggerProvider());
            $this->assertSame($eventLoggerProvider, Globals::eventLoggerProvider());
            $this->assertSame($responsePropagator, Globals::responsePropagator());
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
        $logger = $this->createMock(LoggerInterface::class);
        $loggerProvider = $this->createMock(LoggerProviderInterface::class);
        $loggerProvider->method('getLogger')->willReturn($logger);
        $eventLogger = $this->createMock(EventLoggerInterface::class);
        $eventLoggerProvider = $this->createMock(EventLoggerProviderInterface::class);
        $eventLoggerProvider->method('getEventLogger')->willReturn($eventLogger);
        $propagator = $this->createMock(TextMapPropagatorInterface::class);
        $responsePropagator = $this->createMock(ResponsePropagatorInterface::class);

        $scope = Configurator::create()
            ->withTracerProvider($tracerProvider)
            ->withMeterProvider($meterProvider)
            ->withLoggerProvider($loggerProvider)
            ->withEventLoggerProvider($eventLoggerProvider)
            ->withPropagator($propagator)
            ->withResponsePropagator($responsePropagator)
            ->activate();

        try {
            $this->assertSame($tracer, $instrumentation->tracer());
            $this->assertSame($meter, $instrumentation->meter());
            $this->assertSame($logger, $instrumentation->logger());
            $this->assertSame($eventLogger, $instrumentation->eventLogger());
        } finally {
            $scope->detach();
        }
    }

    public function test_initializers(): void
    {
        $called = false;
        $closure = function (Configurator $configurator) use (&$called): Configurator {
            $called = true;

            return $configurator;
        };
        Globals::registerInitializer($closure);
        $this->assertFalse($called);
        Globals::propagator();
        $this->assertTrue($called); //@phpstan-ignore-line
    }

    public function test_initializer_error(): void
    {
        $closure = function (Configurator $configurator): Configurator {
            throw new \Exception('kaboom');
        };
        Globals::registerInitializer($closure);
        $this->logWriter->expects($this->once())->method('write')->with(
            $this->equalTo(LogLevel::WARNING),
            $this->anything(),
            $this->anything(),
        );
        Globals::propagator();
    }
}
