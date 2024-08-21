<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\InstrumentationScope;

use OpenTelemetry\SDK\Common\InstrumentationScope\Config;
use OpenTelemetry\SDK\Common\InstrumentationScope\ConfigTrait;
use OpenTelemetry\SDK\Trace\TracerConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigTrait::class)]
class ConfigTest extends TestCase
{
    private Config $config;

    public function setUp(): void
    {
        $this->config = new class() implements Config {
            use ConfigTrait;
        };
    }

    #[DataProvider('enabledProvider')]
    public function test_is_enabled(bool $disabled, bool $expected): void
    {
        $this->config->setDisabled($disabled);
        $this->assertSame($expected, $this->config->isEnabled());
    }

    public static function enabledProvider(): array
    {
        return [
            [false, true],
            [true, false],
        ];
    }

    public function test_default_is_enabled(): void
    {
        $config = TracerConfig::default();
        $this->assertTrue($config->isEnabled());
    }
}
