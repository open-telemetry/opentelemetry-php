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
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use RuntimeException;

#[CoversClass(SdkBuilder::class)]
class SdkBuilderTest extends TestCase
{
    use TestState;

    /**
     * @var \PHPUnit\Framework\MockObject\Stub&\OpenTelemetry\Context\Propagation\TextMapPropagatorInterface
     */
    private \PHPUnit\Framework\MockObject\Stub $propagator;
    /**
     * @var \PHPUnit\Framework\MockObject\Stub&\OpenTelemetry\SDK\Trace\TracerProviderInterface
     */
    private \PHPUnit\Framework\MockObject\Stub $tracerProvider;
    /**
     * @var \PHPUnit\Framework\MockObject\Stub&\OpenTelemetry\SDK\Metrics\MeterProviderInterface
     */
    private \PHPUnit\Framework\MockObject\Stub $meterProvider;
    /**
     * @var \PHPUnit\Framework\MockObject\Stub&\OpenTelemetry\SDK\Logs\LoggerProviderInterface
     */
    private \PHPUnit\Framework\MockObject\Stub $loggerProvider;
    /**
     * @var \PHPUnit\Framework\MockObject\Stub&\OpenTelemetry\API\Logs\EventLoggerProviderInterface
     */
    private \PHPUnit\Framework\MockObject\Stub $eventLoggerProvider;
    /**
     * @var \PHPUnit\Framework\MockObject\Stub&\OpenTelemetry\Context\Propagation\ResponsePropagatorInterface
     */
    private \PHPUnit\Framework\MockObject\Stub $responsePropagator;
    private SdkBuilder $builder;

    #[\Override]
    public function setUp(): void
    {
        $this->propagator = $this->createStub(TextMapPropagatorInterface::class);
        $this->tracerProvider = $this->createStub(TracerProviderInterface::class);
        $this->meterProvider = $this->createStub(MeterProviderInterface::class);
        $this->loggerProvider = $this->createStub(LoggerProviderInterface::class);
        $this->eventLoggerProvider = $this->createStub(EventLoggerProviderInterface::class);
        $this->responsePropagator = $this->createStub(ResponsePropagatorInterface::class);
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
        $default = Logging::logLevel();

        $this->builder->setLogLevel(LogLevel::EMERGENCY)->build();

        $this->assertSame($default, Logging::logLevel(), 'building alone leaves global logging untouched');
    }

    public function test_build_and_register_global_applies_log_level(): void
    {
        $scope = $this->builder->setLogLevel(LogLevel::EMERGENCY)->buildAndRegisterGlobal();

        $this->assertSame(Logging::level(LogLevel::EMERGENCY), Logging::logLevel());

        $scope->detach();
    }

    public function test_build_and_register_global_without_log_level_keeps_default(): void
    {
        $default = Logging::logLevel();

        $scope = $this->builder->buildAndRegisterGlobal();

        $this->assertSame($default, Logging::logLevel());

        $scope->detach();
    }

    /**
     * The level is process-global, so it must have the same lifetime as the global registration it
     * accompanies rather than outliving the SDK that asked for it.
     */
    public function test_detaching_restores_the_previous_log_level(): void
    {
        Logging::setLogLevel(LogLevel::WARNING);

        $scope = $this->builder->setLogLevel(LogLevel::EMERGENCY)->buildAndRegisterGlobal();
        $this->assertSame(Logging::level(LogLevel::EMERGENCY), Logging::logLevel());

        $scope->detach();
        $this->assertSame(Logging::level(LogLevel::WARNING), Logging::logLevel());
    }

    /**
     * The level is set before the SDK is built, so a failure part way through must not leave it
     * behind.
     */
    public function test_failed_registration_restores_the_previous_log_level(): void
    {
        Logging::setLogLevel(LogLevel::WARNING);

        $builder = new class() extends SdkBuilder {
            #[\Override]
            public function build(): Sdk
            {
                throw new RuntimeException('cannot build');
            }
        };

        $this->expectException(RuntimeException::class);

        try {
            $builder->setLogLevel(LogLevel::EMERGENCY)->buildAndRegisterGlobal();
        } finally {
            $this->assertSame(Logging::level(LogLevel::WARNING), Logging::logLevel());
        }
    }
}
