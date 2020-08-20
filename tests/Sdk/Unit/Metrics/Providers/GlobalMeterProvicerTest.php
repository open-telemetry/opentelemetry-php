<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Metrics\Providers;

use OpenTelemetry\Sdk\Metrics\Meter;
use OpenTelemetry\Sdk\Metrics\Providers\GlobalMeterProvider;
use OpenTelemetry\Sdk\Metrics\Providers\MeterProvider;
use PHPUnit\Framework\TestCase;

class GlobalMeterProviderTest extends TestCase
{
    public function testGLobalMeterProviderSettersAndGetters()
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
