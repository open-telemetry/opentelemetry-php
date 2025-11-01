<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\API\Logs\EventLoggerProviderInterface;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\SdkBuilder;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Sdk::class)]
class SdkTest extends TestCase
{
    use TestState;

    private TextMapPropagatorInterface $propagator;
    private MeterProviderInterface $meterProvider;
    private TracerProviderInterface $tracerProvider;
    private LoggerProviderInterface $loggerProvider;
    private EventLoggerProviderInterface $eventLoggerProvider;
    private ResponsePropagatorInterface $responsePropagator;

    #[\Override]
    public function setUp(): void
    {
        $this->propagator = $this->createMock(TextMapPropagatorInterface::class);
        $this->meterProvider = $this->createMock(MeterProviderInterface::class);
        $this->tracerProvider = $this->createMock(TracerProviderInterface::class);
        $this->loggerProvider = $this->createMock(LoggerProviderInterface::class);
        $this->eventLoggerProvider = $this->createMock(EventLoggerProviderInterface::class);
        $this->responsePropagator = $this->createMock(ResponsePropagatorInterface::class);
    }

    #[\Override]
    public function tearDown(): void
    {
        self::restoreEnvironmentVariables();
    }

    public function test_is_not_disabled_by_default(): void
    {
        $this->assertFalse(Sdk::isDisabled());
    }

    #[DataProvider('disabledProvider')]
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

    #[DataProvider('instrumentationDisabledProvider')]
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
            ['all', 'foo', true],
            ['all,bar', 'foo', false],
            ['all,foo', 'foo', true],
        ];
    }

    public function test_builder(): void
    {
        $this->assertInstanceOf(SdkBuilder::class, Sdk::builder());
    }

    public function test_getters(): void
    {
        $sdk = new Sdk($this->tracerProvider, $this->meterProvider, $this->loggerProvider, $this->eventLoggerProvider, $this->propagator, $this->responsePropagator);
        $this->assertSame($this->propagator, $sdk->getPropagator());
        $this->assertSame($this->meterProvider, $sdk->getMeterProvider());
        $this->assertSame($this->tracerProvider, $sdk->getTracerProvider());
        $this->assertSame($this->loggerProvider, $sdk->getLoggerProvider());
        $this->assertSame($this->eventLoggerProvider, $sdk->getEventLoggerProvider());
        $this->assertSame($this->responsePropagator, $sdk->getResponsePropagator());
    }
}
