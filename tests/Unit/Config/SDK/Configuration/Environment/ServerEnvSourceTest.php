<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\Configuration\Environment;

use OpenTelemetry\Config\SDK\Configuration\Environment\ServerEnvSource;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ServerEnvSource::class)]
#[BackupGlobals(true)]
final class ServerEnvSourceTest extends TestCase
{
    public function test_read_raw_returns_value_from_server(): void
    {
        $_SERVER['TEST_SERVER_VAR'] = 'server_value';

        $source = new ServerEnvSource();
        $this->assertSame('server_value', $source->readRaw('TEST_SERVER_VAR'));
    }

    public function test_read_raw_returns_null_for_missing_key(): void
    {
        unset($_SERVER['NONEXISTENT_SERVER_VAR']);

        $source = new ServerEnvSource();
        $this->assertNull($source->readRaw('NONEXISTENT_SERVER_VAR'));
    }

    public function test_read_raw_returns_non_string_values(): void
    {
        $_SERVER['TEST_INT_VAR'] = 42;

        $source = new ServerEnvSource();
        $this->assertSame(42, $source->readRaw('TEST_INT_VAR'));
    }
}
