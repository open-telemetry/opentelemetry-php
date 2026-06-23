<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\SDK\Common\InstrumentationScope\Config;
use OpenTelemetry\SDK\Logs\LoggerConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LoggerConfig::class)]
class LoggerConfigTest extends TestCase
{
    public function test_default_returns_instance(): void
    {
        $config = LoggerConfig::default();

        $this->assertInstanceOf(LoggerConfig::class, $config);
        $this->assertInstanceOf(Config::class, $config);
    }

    public function test_is_enabled_by_default(): void
    {
        $config = LoggerConfig::default();

        $this->assertTrue($config->isEnabled());
    }

    public function test_set_disabled(): void
    {
        $config = LoggerConfig::default();
        $config->setDisabled(true);

        $this->assertFalse($config->isEnabled());
    }

    public function test_set_disabled_false_re_enables(): void
    {
        $config = LoggerConfig::default();
        $config->setDisabled(true);
        $config->setDisabled(false);

        $this->assertTrue($config->isEnabled());
    }
}
