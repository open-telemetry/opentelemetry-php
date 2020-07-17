<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Metrics\Providers\GlobalMeterProvider;
use OpenTelemetry\Sdk\Metrics\Providers\MeterProvider;
use PHPUnit\Framework\TestCase;

class GlobalMeterProvicerTest extends TestCase
{
    public function testGLobalMeterProviderSettersAndGetters()
    {
        $defaultProvider = GlobalMeterProvider::getGlobalProvider();

        $meter = GlobalMeterProvider::getMeter('test');

        $customGlobalProvider = new MeterProvider();

        GlobalMeterProvider::setGlobalProvider($customGlobalProvider);

        $this->assertSame($customGlobalProvider, GlobalMeterProvider::getGlobalProvider());
    }
}
