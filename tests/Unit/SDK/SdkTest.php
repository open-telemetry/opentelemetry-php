<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\SdkBuilder;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Sdk
 */
class SdkTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        self::restoreEnvironmentVariables();
    }

    public function test_is_not_disabled_by_default(): void
    {
        $this->assertFalse(Sdk::isDisabled());
    }

    /**
     * @dataProvider disabledProvider
     */
    public function test_is_disabled(string $value, bool $expected): void
    {
        self::setEnvironmentVariable('OTEL_SDK_DISABLED', $value);
        $this->assertSame($expected, Sdk::isDisabled());
    }
    public function disabledProvider(): array
    {
        return [
            ['true', true],
            ['1', true],
            ['false', false],
            ['0', false],
        ];
    }

    public function test_builder(): void
    {
        $this->assertInstanceOf(SdkBuilder::class, Sdk::builder());
    }

    public function test_getters(): void
    {
        $propagator = $this->createMock(TextMapPropagatorInterface::class);
        $meterProvider = $this->createMock(MeterProviderInterface::class);
        $tracerProvider = $this->createMock(TracerProviderInterface::class);
        $sdk = new Sdk($tracerProvider, $meterProvider, $propagator);
        $this->assertSame($propagator, $sdk->getPropagator());
        $this->assertSame($meterProvider, $sdk->getMeterProvider());
        $this->assertSame($tracerProvider, $sdk->getTracerProvider());
    }
}
