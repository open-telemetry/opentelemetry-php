<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\API\Baggage\Propagation\BaggagePropagator;
use OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SDK\Common\Environment\Variables;
use OpenTelemetry\SDK\SdkAutoloader;
use OpenTelemetry\SDK\SdkBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\SdkAutoloader
 */
class SdkAutoloaderTest extends TestCase
{
    use EnvironmentVariables;

    private CachedInstrumentation $instrumentation;

    public function setUp(): void
    {
        $this->instrumentation = new CachedInstrumentation('test');
    }

    public function tearDown(): void
    {
        SdkAutoloader::shutdown();
        $this->restoreEnvironmentVariables();
    }

    public function test_disabled_by_default(): void
    {
        $this->assertFalse(SdkAutoloader::autoload());
        $this->assertSame(NoopTracer::getInstance(), $this->instrumentation->tracer());
    }

    public function test_enabled(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_AUTOLOAD_ENABLED', 'true');
        $this->assertTrue(SdkAutoloader::autoload());
    }

    public function test_autoload_twice(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_AUTOLOAD_ENABLED', 'true');
        $this->assertTrue(SdkAutoloader::autoload());
        $this->assertFalse(SdkAutoloader::autoload());
    }

    public function test_builds_sdk_from_environment(): void
    {
        $builder = $this->createMock(SdkBuilder::class);
        $builder->method('setTracerProvider')->willReturnSelf();
        $builder->method('setPropagator')->willReturnSelf();
        $builder->method('setMeterProvider')->willReturnSelf();
        $builder->method('setAutoShutdown')->willReturnSelf();
        $builder->method('buildAndRegisterGlobal')->willReturn($this->createMock(ScopeInterface::class));
        $this->setEnvironmentVariable('OTEL_PHP_AUTOLOAD_ENABLED', 'true');
        $this->setEnvironmentVariable(Variables::OTEL_PROPAGATORS, 'baggage');
        $builder->expects($this->once())->method('setPropagator')->with($this->callback(function ($propagator) {
            $this->assertInstanceOf(BaggagePropagator::class, $propagator);

            return true;
        }));
        SdkAutoloader::autoload($builder);
    }
}
