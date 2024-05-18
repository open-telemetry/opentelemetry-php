<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Instrumentation\AutoInstrumentation;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManager;
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
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\SdkAutoloader;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(LateBindingLoggerProvider::class)]
#[CoversClass(LateBindingLogger::class)]
#[CoversClass(LateBindingMeterProvider::class)]
#[CoversClass(LateBindingMeter::class)]
#[CoversClass(LateBindingTracerProvider::class)]
#[CoversClass(LateBindingTracer::class)]
#[CoversMethod(SdkAutoloader::class, 'createLateBindingLoggerProvider')]
#[CoversMethod(SdkAutoloader::class, 'createLateBindingMeterProvider')]
#[CoversMethod(SdkAutoloader::class, 'createLateBindingTracerProvider')]
class LateBindingProviderTest extends TestCase
{
    use TestState;

    public function setUp(): void
    {
        Logging::disable();
    }

    public function test_late_binding_providers(): void
    {
        $instrumentation = new class() implements Instrumentation {
            private static ?Context $context;
            public function register(HookManager $hookManager, ConfigurationRegistry $configuration, Context $context): void
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
        };
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $tracer_called = false;
        $logger_called = false;
        $meter_called = false;
        //the "real" tracer+meter+logger, which will be accessed through late binding providers
        $tracerProvider = $this->createMock(TracerProviderInterface::class);
        $tracerProvider->method('getTracer')->willReturnCallback(function () use (&$tracer_called): TracerInterface {
            $tracer_called = true;

            return $this->createMock(TracerInterface::class);
        });
        $meterProvider = $this->createMock(MeterProviderInterface::class);
        $meterProvider->method('getMeter')->willReturnCallback(function () use (&$meter_called): MeterInterface {
            $meter_called = true;

            return $this->createMock(MeterInterface::class);
        });
        $loggerProvider = $this->createMock(LoggerProviderInterface::class);
        $loggerProvider->method('getLogger')->willReturnCallback(function () use (&$logger_called): LoggerInterface {
            $logger_called = true;

            return $this->createMock(LoggerInterface::class);
        });
        ServiceLoader::register(Instrumentation::class, $instrumentation::class);
        //@todo reset?
        $this->assertTrue(SdkAutoloader::autoload());
        //tracer initializer added _after_ autoloader has run and instrumentation registered
        Globals::registerInitializer(function (Configurator $configurator) use ($tracerProvider, $loggerProvider, $meterProvider): Configurator {
            return $configurator
                ->withTracerProvider($tracerProvider)
                ->withMeterProvider($meterProvider)
                ->withLoggerProvider($loggerProvider)
            ;
        });

        $this->assertFalse($tracer_called);
        $tracer = $instrumentation->getTracer();
        $this->assertFalse($tracer_called);
        $tracer->spanBuilder('test-span')->startSpan(); /** @phpstan-ignore-next-line */
        $this->assertTrue($tracer_called);

        $this->assertFalse($meter_called);
        $meter = $instrumentation->getMeter();
        $this->assertFalse($meter_called);
        $meter->createCounter('cnt'); /** @phpstan-ignore-next-line */
        $this->assertTrue($meter_called);

        $this->assertFalse($logger_called);
        $logger = $instrumentation->getLogger();
        $this->assertFalse($logger_called);
        $logger->emit(new LogRecord()); /** @phpstan-ignore-next-line */
        $this->assertTrue($logger_called);
    }
}
