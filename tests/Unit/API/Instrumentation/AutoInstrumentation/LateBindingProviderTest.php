<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Instrumentation\AutoInstrumentation;

use function assert;
use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Configuration\ConfigProperties;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManagerInterface;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\API\Logs\LateBindingLogger;
use OpenTelemetry\API\Logs\LateBindingLoggerProvider;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Metrics\LateBindingMeter;
use OpenTelemetry\API\Metrics\LateBindingMeterProvider;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Trace\LateBindingTracer;
use OpenTelemetry\API\Trace\LateBindingTracerProvider;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;
use OpenTelemetry\SDK\SdkAutoloader;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

#[CoversClass(LateBindingLoggerProvider::class)]
#[CoversClass(LateBindingLogger::class)]
#[CoversClass(LateBindingMeterProvider::class)]
#[CoversClass(LateBindingMeter::class)]
#[CoversClass(LateBindingTracerProvider::class)]
#[CoversClass(LateBindingTracer::class)]
// @todo phpunit 11 (8.2+) only, replace CoversClass(SdkAutoloader::class)
#[CoversClass(SdkAutoloader::class)]
//#[CoversMethod(SdkAutoloader::class, 'createLateBindingLoggerProvider')]
//#[CoversMethod(SdkAutoloader::class, 'createLateBindingMeterProvider')]
//#[CoversMethod(SdkAutoloader::class, 'createLateBindingTracerProvider')]
class LateBindingProviderTest extends TestCase
{
    use TestState;
    use ProphecyTrait;

    #[\Override]
    public function setUp(): void
    {
        Logging::disable();
    }

    public function test_late_binding_providers(): void
    {
        $instrumentation = new class() implements Instrumentation {
            private static ?Context $context;
            #[\Override]
            public function register(HookManagerInterface $hookManager, ConfigProperties $configuration, Context $context): void
            {
                self::$context = $context;
            }
            public function getTracer(): TracerInterface
            {
                assert(self::$context !== null);

                return self::$context->tracerProvider->getTracer('test');
            }
            public function getMeter(): MeterInterface
            {
                assert(self::$context !== null);

                return self::$context->meterProvider->getMeter('test');
            }
            public function getLogger(): LoggerInterface
            {
                assert(self::$context !== null);

                return self::$context->loggerProvider->getLogger('test');
            }
            public function getPropagator(): TextMapPropagatorInterface
            {
                assert(self::$context !== null);

                return self::$context->propagator;
            }
            public function getResponsePropagator(): ResponsePropagatorInterface
            {
                assert(self::$context !== null);

                return self::$context->responsePropagator;
            }
        };
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $tracer_accessed = false;
        $logger_accessed = false;
        $meter_accessed = false;

        $tracerProvider = $this->createMock(TracerProviderInterface::class);
        $tracerProvider->method('getTracer')->willReturnCallback(function () use (&$tracer_accessed): TracerInterface {
            $tracer_accessed = true;

            return $this->createMock(TracerInterface::class);
        });
        $meterProvider = $this->createMock(MeterProviderInterface::class);
        $meterProvider->method('getMeter')->willReturnCallback(function () use (&$meter_accessed): MeterInterface {
            $meter_accessed = true;

            return $this->createMock(MeterInterface::class);
        });
        $loggerProvider = $this->createMock(LoggerProviderInterface::class);
        $loggerProvider->method('getLogger')->willReturnCallback(function () use (&$logger_accessed): LoggerInterface {
            $logger_accessed = true;

            return $this->createMock(LoggerInterface::class);
        });
        $propagator = $this->prophesize(TextMapPropagatorInterface::class);
        $responsePropagator = $this->prophesize(ResponsePropagatorInterface::class);

        ServiceLoader::register(Instrumentation::class, $instrumentation::class);
        $this->assertTrue(SdkAutoloader::autoload());
        //initializer added _after_ autoloader has run and instrumentation registered
        Globals::registerInitializer(function (Configurator $configurator) use ($tracerProvider, $loggerProvider, $meterProvider, $propagator, $responsePropagator): Configurator {
            return $configurator
                ->withTracerProvider($tracerProvider)
                ->withMeterProvider($meterProvider)
                ->withLoggerProvider($loggerProvider)
                ->withPropagator($propagator->reveal())
                ->withResponsePropagator($responsePropagator->reveal())
            ;
        });

        $this->assertFalse($tracer_accessed);
        $tracer = $instrumentation->getTracer();
        $this->assertFalse($tracer_accessed);
        $tracer->spanBuilder('test-span'); /** @phpstan-ignore-next-line */
        $this->assertTrue($tracer_accessed);

        $this->assertFalse($meter_accessed);
        $meter = $instrumentation->getMeter();
        $this->assertFalse($meter_accessed);
        $meter->createCounter('cnt'); /** @phpstan-ignore-next-line */
        $this->assertTrue($meter_accessed);

        $this->assertFalse($logger_accessed);
        $logger = $instrumentation->getLogger();
        $this->assertFalse($logger_accessed);
        $logger->emit(new LogRecord()); /** @phpstan-ignore-next-line */
        $this->assertTrue($logger_accessed);

        /** @phpstan-ignore-next-line */
        $propagator->fields()->shouldNotHaveBeenCalled();
        $instrumentation->getPropagator()->fields();
        /** @phpstan-ignore-next-line */
        $propagator->fields()->shouldHaveBeenCalledOnce();
    }

    public function test_late_binding_meter_observable_instruments(): void
    {
        $this->expectNotToPerformAssertions();

        $meterProvider = new LateBindingMeterProvider(static fn () => new NoopMeterProvider());
        $meterProvider->getMeter('test')->createObservableCounter('test');
        $meterProvider->getMeter('test')->createObservableGauge('test');
        $meterProvider->getMeter('test')->createObservableUpDownCounter('test');
    }
}
