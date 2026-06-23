<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopMeterProvider::class)]
class NoopMeterProviderTest extends TestCase
{
    public function test_shutdown_returns_true(): void
    {
        $provider = new NoopMeterProvider();
        $this->assertTrue($provider->shutdown());
    }

    public function test_force_flush_returns_true(): void
    {
        $provider = new NoopMeterProvider();
        $this->assertTrue($provider->forceFlush());
    }

    public function test_get_meter_returns_meter(): void
    {
        $provider = new NoopMeterProvider();
        $this->assertInstanceOf(MeterInterface::class, $provider->getMeter('test'));
    }

    public function test_get_meter_with_all_params(): void
    {
        $provider = new NoopMeterProvider();
        $meter = $provider->getMeter('test', '1.0', 'https://schema', []);
        $this->assertInstanceOf(MeterInterface::class, $meter);
    }

    public function test_update_configurator_does_nothing(): void
    {
        $provider = new NoopMeterProvider();
        $provider->updateConfigurator(Configurator::meter());
        $this->assertTrue(true);
    }
}
