<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
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
        $this->restoreEnvironmentVariables();
    }

    public function test_disabled_by_default(): void
    {
        $this->assertFalse(SdkAutoloader::autoload());
    }

    public function test_enabled(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $this->assertTrue(SdkAutoloader::autoload());
    }

    public function test_disabled_with_invalid_flag(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'invalid-value');
        $this->assertFalse(SdkAutoloader::autoload());
    }
}
