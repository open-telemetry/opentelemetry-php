<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\API\Globals;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\SdkBuilder;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\SdkBuilder
 */
class SdkBuilderTest extends TestCase
{
    private TextMapPropagatorInterface $propagator;
    private TracerProviderInterface $tracerProvider;
    private MeterProviderInterface $meterProvider;
    private LoggerProviderInterface $loggerProvider;
    private SdkBuilder $builder;

    public function setUp(): void
    {
        $this->propagator = $this->createMock(TextMapPropagatorInterface::class);
        $this->tracerProvider = $this->createMock(TracerProviderInterface::class);
        $this->meterProvider = $this->createMock(MeterProviderInterface::class);
        $this->loggerProvider = $this->createMock(LoggerProviderInterface::class);
        $this->builder = (new SdkBuilder())
            ->setMeterProvider($this->meterProvider)
            ->setLoggerProvider($this->loggerProvider)
            ->setPropagator($this->propagator)
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
    }

    public function test_build_and_register_global(): void
    {
        $scope = $this->builder->buildAndRegisterGlobal();
        $this->assertSame($this->meterProvider, Globals::meterProvider());
        $this->assertSame($this->propagator, Globals::propagator());
        $this->assertSame($this->tracerProvider, Globals::tracerProvider());
        $this->assertSame($this->loggerProvider, Globals::loggerProvider());
        $scope->detach();
    }
}
