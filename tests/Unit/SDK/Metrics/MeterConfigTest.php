<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\SDK\Common\InstrumentationScope\Config;
use OpenTelemetry\SDK\Metrics\MeterConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MeterConfig::class)]
class MeterConfigTest extends TestCase
{
    public function test_default_returns_instance(): void
    {
        $config = MeterConfig::default();

        $this->assertInstanceOf(MeterConfig::class, $config);
        $this->assertInstanceOf(Config::class, $config);
    }

    public function test_is_enabled_by_default(): void
    {
        $config = MeterConfig::default();

        $this->assertTrue($config->isEnabled());
    }

    public function test_set_disabled(): void
    {
        $config = MeterConfig::default();
        $config->setDisabled(true);

        $this->assertFalse($config->isEnabled());
    }

    public function test_set_disabled_false_re_enables(): void
    {
        $config = MeterConfig::default();
        $config->setDisabled(true);
        $config->setDisabled(false);

        $this->assertTrue($config->isEnabled());
    }
}
