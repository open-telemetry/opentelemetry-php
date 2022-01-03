<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Metrics\Providers;

use OpenTelemetry\SDK\Metrics\Meter;
use OpenTelemetry\SDK\Metrics\Providers\GlobalMeterProvider;
use OpenTelemetry\SDK\Metrics\Providers\MeterProvider;
use PHPUnit\Framework\TestCase;

class GlobalMeterProviderTest extends TestCase
{
    public function test_global_meter_provider_setters_and_getters()
    {
        $defaultProvider = GlobalMeterProvider::getGlobalProvider();

        $this->assertInstanceOf(MeterProvider::class, $defaultProvider);

        $meter = GlobalMeterProvider::getMeter('test');

        $this->assertInstanceOf(Meter::class, $meter);

        $customGlobalProvider = new MeterProvider();

        GlobalMeterProvider::setGlobalProvider($customGlobalProvider);

        $this->assertSame($customGlobalProvider, GlobalMeterProvider::getGlobalProvider());
    }
}
