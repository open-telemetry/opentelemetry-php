<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\API\Globals;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
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
    private SdkBuilder $builder;

    public function setUp(): void
    {
        $this->propagator = $this->createMock(TextMapPropagatorInterface::class);
        $this->tracerProvider = $this->createMock(TracerProviderInterface::class);
        $this->meterProvider = $this->createMock(MeterProviderInterface::class);
        $this->builder = (new SdkBuilder())
            ->setMeterProvider($this->meterProvider)
            ->setPropagator($this->propagator)
            ->setTracerProvider($this->tracerProvider);
    }

    public function test_build(): void
    {
        $sdk = $this->builder->build();
        $this->assertSame($this->meterProvider, $sdk->getMeterProvider());
        $this->assertSame($this->propagator, $sdk->getPropagator());
        $this->assertSame($this->tracerProvider, $sdk->getTracerProvider());
    }

    public function test_build_and_register_global(): void
    {
        $scope = $this->builder->buildAndRegisterGlobal();
        $this->assertSame($this->meterProvider, Globals::meterProvider());
        $this->assertSame($this->propagator, Globals::propagator());
        $this->assertSame($this->tracerProvider, Globals::tracerProvider());
        $scope->detach();
    }
}
