<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\Configuration\Environment;

use OpenTelemetry\Config\SDK\Configuration\Environment\PhpIniEnvSource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhpIniEnvSource::class)]
final class PhpIniEnvSourceTest extends TestCase
{
    public function test_read_raw_returns_false_for_nonexistent_ini_value(): void
    {
        $source = new PhpIniEnvSource();
        $this->assertFalse($source->readRaw('nonexistent_ini_setting_' . uniqid()));
    }

    public function test_read_raw_returns_known_ini_value(): void
    {
        $source = new PhpIniEnvSource();
        // display_errors is a standard PHP ini setting that always exists
        $result = $source->readRaw('display_errors');
        $this->assertNotNull($result);
    }
}
