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

    /**
     * @dataProvider configurationProvider
     */
    public function test_enabled_by_configuration(string $autoload, string $disabled, bool $expected): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, $autoload);
        $this->setEnvironmentVariable(Variables::OTEL_SDK_DISABLED, $disabled);
        $this->assertSame($expected, SdkAutoloader::autoload());
    }

    public function configurationProvider(): array
    {
        return [
            'autoload enabled, sdk not disabled' => ['true', 'false', true],
            'autoload enabled, sdk disabled' => ['true', 'true', false],
            'autoload disabled, sdk disabled' => ['false', 'true', false],
            'autoload disabled, sdk not disabled' => ['false', 'false', false],
        ];
    }

    public function test_disabled_with_invalid_flag(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'invalid-value');
        $this->assertFalse(SdkAutoloader::autoload());
    }
}
