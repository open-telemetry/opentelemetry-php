<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\InstrumentationScope;

use OpenTelemetry\SDK\Common\InstrumentationScope\Config;
use OpenTelemetry\SDK\Common\InstrumentationScope\State;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Config::class)]
class ConfigTest extends TestCase
{
    #[DataProvider('enabledProvider')]
    public function test_is_enabled(State $state, bool $expected): void
    {
        $config = new Config($state);
        $this->assertSame($expected, $config->isEnabled());
    }

    public static function enabledProvider(): array
    {
        return [
            [State::ENABLED, true],
            [State::DISABLED, false],
        ];
    }

    public function test_default_is_enabled(): void
    {
        $config = Config::default();
        $this->assertTrue($config->isEnabled());
    }
}
