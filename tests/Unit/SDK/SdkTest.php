<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\SDK\SDK;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\SDK
 */
class SdkTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        self::restoreEnvironmentVariables();
    }

    public function test_is_not_disabled_by_default(): void
    {
        $this->assertFalse(SDK::isDisabled());
    }

    /**
     * @dataProvider disabledProvider
     */
    public function test_is_disabled(string $value, bool $expected): void
    {
        self::setEnvironmentVariable('OTEL_SDK_DISABLED', $value);
        $this->assertSame($expected, SDK::isDisabled());
    }
    public function disabledProvider(): array
    {
        return [
            ['true', true],
            ['1', true],
            ['false', false],
            ['0', false],
        ];
    }
}
