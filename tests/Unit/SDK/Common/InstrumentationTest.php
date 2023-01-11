<?php

declare(strict_types=1);

namespace OpenTelemetry\Example\Unit\SDK\Common;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Instrumentation;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Instrumentation
 */
class InstrumentationTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @dataProvider isDisabledProvider
     */
    public function test_is_disabled(string $value, string $name, bool $expected): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_DISABLED_INSTRUMENTATIONS, $value);

        $this->assertSame($expected, Instrumentation::isDisabled($name));
    }

    public function isDisabledProvider(): array
    {
        return [
            ['foo,bar', 'foo', true],
            ['foo,bar', 'bar', true],
            ['', 'foo', false],
            ['foo', 'foo', true],
        ];
    }
}
