<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Logs\EventLoggerProviderInterface;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\SdkBuilder;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use RuntimeException;

#[CoversClass(SdkBuilder::class)]
class SdkBuilderTest extends TestCase
{
    private TextMapPropagatorInterface $propagator;
    private TracerProviderInterface $tracerProvider;
    private MeterProviderInterface $meterProvider;
    private LoggerProviderInterface $loggerProvider;
    private EventLoggerProviderInterface $eventLoggerProvider;
    private ResponsePropagatorInterface $responsePropagator;
    private SdkBuilder $builder;

    #[\Override]
    public function setUp(): void
    {
        $this->propagator = $this->createMock(TextMapPropagatorInterface::class);
        $this->tracerProvider = $this->createMock(TracerProviderInterface::class);
        $this->meterProvider = $this->createMock(MeterProviderInterface::class);
        $this->loggerProvider = $this->createMock(LoggerProviderInterface::class);
        $this->eventLoggerProvider = $this->createMock(EventLoggerProviderInterface::class);
        $this->responsePropagator = $this->createMock(ResponsePropagatorInterface::class);
        $this->builder = (new SdkBuilder())
            ->setMeterProvider($this->meterProvider)
            ->setLoggerProvider($this->loggerProvider)
            ->setEventLoggerProvider($this->eventLoggerProvider)
            ->setPropagator($this->propagator)
            ->setResponsePropagator($this->responsePropagator)
            ->setTracerProvider($this->tracerProvider)
            ->setAutoShutdown(true);
    }

    public function test_build(): void
    {
        $sdk = $this->builder->build();
        $this->assertSame($this->meterProvider, $sdk->getMeterProvider());
        $this->assertSame($this->propagator, $sdk->getPropagator());
        $this->assertSame($this->tracerProvider, $sdk->getTracerProvider());
        $this->assertSame($this->loggerProvider, $sdk->getLoggerProvider());
        $this->assertSame($this->eventLoggerProvider, $sdk->getEventLoggerProvider());
        $this->assertSame($this->responsePropagator, $sdk->getResponsePropagator());
    }

    public function test_build_and_register_global(): void
    {
        $scope = $this->builder->buildAndRegisterGlobal();
        $this->assertSame($this->meterProvider, Globals::meterProvider());
        $this->assertSame($this->propagator, Globals::propagator());
        $this->assertSame($this->tracerProvider, Globals::tracerProvider());
        $this->assertSame($this->loggerProvider, Globals::loggerProvider());
        $this->assertSame($this->eventLoggerProvider, Globals::eventLoggerProvider());
        $this->assertSame($this->responsePropagator, Globals::responsePropagator());
        $scope->detach();
    }

    public function test_build_does_not_apply_log_level(): void
    {
        Logging::reset();
        $default = Logging::logLevel();

        $this->builder->setLogLevel(LogLevel::EMERGENCY)->build();

        $this->assertSame($default, Logging::logLevel(), 'building alone leaves global logging untouched');

        Logging::reset();
    }

    public function test_build_and_register_global_applies_log_level(): void
    {
        Logging::reset();

        $scope = $this->builder->setLogLevel(LogLevel::EMERGENCY)->buildAndRegisterGlobal();

        $this->assertSame(Logging::level(LogLevel::EMERGENCY), Logging::logLevel());

        $scope->detach();
        Logging::reset();
    }

    public function test_build_and_register_global_without_log_level_keeps_default(): void
    {
        Logging::reset();
        $default = Logging::logLevel();

        $scope = $this->builder->buildAndRegisterGlobal();

        $this->assertSame($default, Logging::logLevel());

        $scope->detach();
        Logging::reset();
    }

    /**
     * The level is process-global, so it must have the same lifetime as the global registration it
     * accompanies rather than outliving the SDK that asked for it.
     */
    public function test_detaching_restores_the_previous_log_level(): void
    {
        Logging::reset();
        Logging::setLogLevel(LogLevel::WARNING);

        $scope = $this->builder->setLogLevel(LogLevel::EMERGENCY)->buildAndRegisterGlobal();
        $this->assertSame(Logging::level(LogLevel::EMERGENCY), Logging::logLevel());

        $scope->detach();
        $this->assertSame(Logging::level(LogLevel::WARNING), Logging::logLevel());

        Logging::reset();
    }

    /**
     * The level is set before the SDK is built, so a failure part way through must not leave it
     * behind.
     */
    public function test_failed_registration_restores_the_previous_log_level(): void
    {
        Logging::reset();
        Logging::setLogLevel(LogLevel::WARNING);

        $builder = new class() extends SdkBuilder {
            #[\Override]
            public function build(): Sdk
            {
                throw new RuntimeException('cannot build');
            }
        };

        try {
            $builder->setLogLevel(LogLevel::EMERGENCY)->buildAndRegisterGlobal();
            $this->fail('registration should have thrown');
        } catch (RuntimeException) {
            $this->assertSame(Logging::level(LogLevel::WARNING), Logging::logLevel());
        } finally {
            Logging::reset();
        }
    }
}
