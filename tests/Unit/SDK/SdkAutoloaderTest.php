<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\API\Common\Instrumentation\Globals;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\SdkAutoloader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\SdkAutoloader
 */
class SdkAutoloaderTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        SdkAutoloader::shutdown();
        Globals::reset();
        $this->restoreEnvironmentVariables();
    }

    public function test_disabled_by_default(): void
    {
        $this->assertFalse(SdkAutoloader::autoload());
        $this->assertInstanceOf(NoopMeterProvider::class, Globals::meterProvider());
        $this->assertInstanceOf(NoopTracerProvider::class, Globals::tracerProvider());
        $this->assertInstanceOf(NoopTextMapPropagator::class, Globals::propagator(), 'propagator not initialized by disabled autoloader');
    }

    public function test_enabled_by_configuration(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        SdkAutoloader::autoload();
        $this->assertNotInstanceOf(NoopTextMapPropagator::class, Globals::propagator());
        $this->assertNotInstanceOf(NoopMeterProvider::class, Globals::meterProvider());
        $this->assertNotInstanceOf(NoopTracerProvider::class, Globals::tracerProvider());
    }

    public function test_sdk_disabled_does_not_disable_propagator(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $this->setEnvironmentVariable(Variables::OTEL_SDK_DISABLED, 'true');
        SdkAutoloader::autoload();
        $this->assertNotInstanceOf(NoopTextMapPropagator::class, Globals::propagator());
        $this->assertInstanceOf(NoopMeterProvider::class, Globals::meterProvider());
        $this->assertInstanceOf(NoopTracerProvider::class, Globals::tracerProvider());
    }

    public function test_disabled_with_invalid_flag(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'invalid-value');
        $this->assertFalse(SdkAutoloader::autoload());
    }
}
