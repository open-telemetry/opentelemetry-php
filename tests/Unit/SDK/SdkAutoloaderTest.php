<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Logs\NoopEventLoggerProvider;
use OpenTelemetry\API\Logs\NoopLoggerProvider;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\SdkAutoloader;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SdkAutoloader::class)]
class SdkAutoloaderTest extends TestCase
{
    use TestState;

    public function setUp(): void
    {
        Logging::disable();
        Globals::reset();
    }

    public function test_disabled_by_default(): void
    {
        $this->assertFalse(SdkAutoloader::isEnabled());
    }

    public function test_enabled(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $this->assertTrue(SdkAutoloader::isEnabled());
    }

    public function test_disabled(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'false');
        $this->assertFalse(SdkAutoloader::isEnabled());
    }

    public function test_disabled_with_invalid_setting(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'invalid-value');
        $this->assertFalse(SdkAutoloader::isEnabled());
    }

    public function test_noop_if_disabled(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'false');
        $this->assertFalse(SdkAutoloader::autoload());
        $this->assertInstanceOf(NoopMeterProvider::class, Globals::meterProvider());
        $this->assertInstanceOf(NoopTracerProvider::class, Globals::tracerProvider());
        $this->assertInstanceOf(NoopLoggerProvider::class, Globals::loggerProvider());
        $this->assertInstanceOf(NoopEventLoggerProvider::class, Globals::eventLoggerProvider());
        $this->assertInstanceOf(NoopTextMapPropagator::class, Globals::propagator(), 'propagator not initialized by disabled autoloader');
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

    public function test_enabled_by_configuration(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        SdkAutoloader::autoload();
        $this->assertNotInstanceOf(NoopTextMapPropagator::class, Globals::propagator());
        $this->assertNotInstanceOf(NoopMeterProvider::class, Globals::meterProvider());
        $this->assertNotInstanceOf(NoopTracerProvider::class, Globals::tracerProvider());
        $this->assertNotInstanceOf(NoopLoggerProvider::class, Globals::loggerProvider());
    }

    public function test_exclude_urls_without_request_uri(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_EXCLUDED_URLS, '.*');
        unset($_SERVER['REQUEST_URI']);
        $this->assertFalse(SdkAutoloader::isExcludedUrl());
    }

    #[DataProvider('excludeUrlsProvider')]
    public function test_exclude_urls(string $exclude, string $uri, bool $expected): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_EXCLUDED_URLS, $exclude);
        $_SERVER['REQUEST_URI'] = $uri;
        $this->assertSame($expected, SdkAutoloader::isExcludedUrl());
    }

    public static function excludeUrlsProvider(): array
    {
        return [
            [
                'foo',
                '/foo?bar=baz',
                true,
            ],
            [
                'foo',
                '/bar',
                false,
            ],
            [
                'foo,bar',
                'https://example.com/bar?p1=2',
                true,
            ],
            [
                'foo,bar',
                'https://example.com/baz?p1=2',
                false,
            ],
            [
                'client/.*/info,healthcheck',
                'https://site/client/123/info',
                true,
            ],
            [
                'client/.*/info,healthcheck',
                'https://site/xyz/healthcheck',
                true,
            ],
        ];
    }

    public function test_enabled_with_excluded_url(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $this->setEnvironmentVariable(Variables::OTEL_PHP_EXCLUDED_URLS, '.*');
        $_SERVER['REQUEST_URI'] = '/test';
        $this->assertFalse(SdkAutoloader::autoload());
    }
}
