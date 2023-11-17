<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
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

    public static function disabledProvider(): array
    {
        return [
            ['true', true],
            ['false', false],
        ];
    }

    /**
     * @dataProvider instrumentationDisabledProvider
     */
    public function test_is_instrumentation_disabled(string $value, string $name, bool $expected): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_DISABLED_INSTRUMENTATIONS, $value);

        $this->assertSame($expected, Sdk::isInstrumentationDisabled($name));
    }

    public static function instrumentationDisabledProvider(): array
    {
        return [
            ['foo,bar', 'foo', true],
            ['foo,bar', 'bar', true],
            ['', 'foo', false],
            ['foo', 'foo', true],
        ];
    }

    /**
     * @dataProvider disabledProvider
     */
    public function test_developer_mode_can_be_toggled(string $value, bool $expected): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_DEVELOPER_MODE_ENABLED, $value);

        $this->assertEquals($expected, Sdk::isDeveloperModeEnabled());
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
        $loggerProvider = $this->createMock(LoggerProviderInterface::class);
        $sdk = new Sdk($tracerProvider, $meterProvider, $loggerProvider, $propagator);
        $this->assertSame($propagator, $sdk->getPropagator());
        $this->assertSame($meterProvider, $sdk->getMeterProvider());
        $this->assertSame($tracerProvider, $sdk->getTracerProvider());
        $this->assertSame($loggerProvider, $sdk->getLoggerProvider());
    }
}
