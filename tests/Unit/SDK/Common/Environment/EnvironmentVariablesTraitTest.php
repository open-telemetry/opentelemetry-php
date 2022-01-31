<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Environment;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Exception;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait
 */
class EnvironmentVariablesTraitTest extends TestCase
{
    use EnvironmentVariables;

    private $mock;

    public function setUp(): void
    {
        $this->mock = new class() {
            use EnvironmentVariablesTrait;
        };
    }
    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function test_environment_variables_integer_get(): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', '100');
        $value = $this->mock->getIntFromEnvironment('OTEL_FOO', 999);
        $this->assertSame(100, $value);
    }

    public function test_environment_variables_integer_failure(): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', 'foo');
        $this->expectException(Exception::class);
        $this->mock->getIntFromEnvironment('OTEL_FOO', 99);
    }

    public function environment_variables_integer_uses_default_if_env_var_not_defined()
    {
        $this->assertSame(20, $this->mock->getIntFromEnvironment('OTEL_FOO', 20));
    }

    public function test_environment_variables_string_get(): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', 'foo');
        $value = $this->mock->getStringFromEnvironment('OTEL_FOO', 'bar');
        $this->assertSame('foo', $value);
    }

    /**
     * The SDK MUST interpret an empty value of an environment variable the same way as when the variable is unset
     * @dataProvider emptyProvider
     */
    public function test_environment_variables_string_uses_default_when_empty_value(?string $input): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', $input);
        $value = $this->mock->getStringFromEnvironment('OTEL_FOO', 'bar');
        $this->assertSame('bar', $value);
    }

    /**
     * @dataProvider emptyProvider
     */
    public function test_environment_variables_int_uses_default_when_empty_value(?string $input): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', $input);
        $value = $this->mock->getIntFromEnvironment('OTEL_FOO', 99);
        $this->assertSame(99, $value);
    }

    /**
     * @dataProvider emptyProvider
     */
    public function test_environment_variables_bool_uses_default_when_empty_value(?string $input): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', $input);
        $value = $this->mock->getBooleanFromEnvironment('OTEL_FOO', true);
        $this->assertTrue($value);
    }

    public function emptyProvider()
    {
        return [
            'no value' => [null],
            'empty string' => [''],
        ];
    }

    /**
     * @dataProvider booleanProvider
     */
    public function test_environment_variables_bool_get(string $input, bool $default, bool $expected)
    {
        $this->setEnvironmentVariable('OTEL_BOOL', $input);
        $this->assertSame($expected, $this->mock->getBooleanFromEnvironment('OTEL_BOOL', $default));
    }

    public function booleanProvider()
    {
        return [
            'false' => ['false', true, false],
            'true' => ['true', false, true],
            'truthy' => ['1', false, true],
            'falsey' => ['0', true, false],
            'TRUE' => ['TRUE', false, true],
            'FALSE' => ['FALSE', true, false],
        ];
    }
}
