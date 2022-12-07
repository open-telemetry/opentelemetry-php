<?php

declare(strict_types=1);

namespace OpenTelemetry\Example\Unit\SDK\Metrics;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Metrics\MeterProviderFactory;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Metrics\MeterProviderFactory
 */
class MeterProviderFactoryTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @dataProvider exporterProvider
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function test_create(string $exporter): void
    {
        $_SERVER[Variables::OTEL_METRICS_EXPORTER] = $exporter;
        $provider = (new MeterProviderFactory())->create();
        $this->assertInstanceOf(MeterInterface::class, $provider->getMeter('test'));
    }

    public function exporterProvider(): array
    {
        return [
            'otlp' => [KnownValues::VALUE_OTLP],
            'none' => [KnownValues::VALUE_NONE],
            'unimplemented' => ['foo'],
        ];
    }

    public function test_sdk_disabled_returns_noop(): void
    {
        $this->setEnvironmentVariable('OTEL_SDK_DISABLED', 'true');
        $provider = (new MeterProviderFactory())->create();
        $this->assertInstanceOf(NoopMeterProvider::class, $provider);
        $this->assertInstanceOf(NoopMeter::class, $provider->getMeter('test'));
    }
}
