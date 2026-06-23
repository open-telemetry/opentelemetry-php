<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\SDK\Common\InstrumentationScope\Config;
use OpenTelemetry\SDK\Trace\TracerConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TracerConfig::class)]
class TracerConfigTest extends TestCase
{
    public function test_default_returns_instance(): void
    {
        $config = TracerConfig::default();

        $this->assertInstanceOf(TracerConfig::class, $config);
        $this->assertInstanceOf(Config::class, $config);
    }

    public function test_is_enabled_by_default(): void
    {
        $config = TracerConfig::default();

        $this->assertTrue($config->isEnabled());
    }

    public function test_set_disabled(): void
    {
        $config = TracerConfig::default();
        $config->setDisabled(true);

        $this->assertFalse($config->isEnabled());
    }

    public function test_set_disabled_false_re_enables(): void
    {
        $config = TracerConfig::default();
        $config->setDisabled(true);
        $config->setDisabled(false);

        $this->assertTrue($config->isEnabled());
    }
}
