<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Exception;
use OpenTelemetry\SDK\EnvironmentVariablesTrait;
use PHPUnit\Framework\TestCase;

class MockWithTrait
{
    use EnvironmentVariablesTrait;
}

class EnvironmentVariablesTraitTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @test
     */
    public function environmentVariables_integer_get()
    {
        $mock = new MockWithTrait();
        $this->setEnvironmentVariable('OTEL_FOO', '100');
        $value = $mock->getIntFromEnvironment('OTEL_FOO', 999);
        $this->assertSame(100, $value);
    }

    /**
     * @test
     */
    public function environmentVariables_integer_failure()
    {
        $mock = new MockWithTrait();
        $this->setEnvironmentVariable('OTEL_FOO', 'foo');
        $this->expectException(Exception::class);
        $mock->getIntFromEnvironment('OTEL_FOO', 99);
    }

    /**
     * @test
     */
    public function environmentVariables_integer_usesDefaultIfEnvVarNotDefined()
    {
        $mock = new MockWithTrait();
        $this->assertSame(20, $mock->getIntFromEnvironment('OTEL_FOO', 20));
    }

    /**
     * @test
     */
    public function environmentVariables_string_get()
    {
        $mock = new MockWithTrait();
        $this->setEnvironmentVariable('OTEL_FOO', 'foo');
        $value = $mock->getStringFromEnvironment('OTEL_FOO', 'bar');
        $this->assertSame('foo', $value);
    }

    /**
     * The SDK MUST interpret an empty value of an environment variable the same way as when the variable is unset
     * @test
     * @dataProvider emptyProvider
     */
    public function environmentVariables_string_usesDefaultWhenEmptyValue(?string $input)
    {
        $mock = new MockWithTrait();
        $this->setEnvironmentVariable('OTEL_FOO', $input);
        $value = $mock->getStringFromEnvironment('OTEL_FOO', 'bar');
        $this->assertSame('bar', $value);
    }

    /*
     * @test
     * @dataProvider emptyProvider
     */
    public function environmentVariables_int_usesDefaultWhenEmptyValue(?int $input)
    {
        $mock = new MockWithTrait();
        $this->setEnvironmentVariable('OTEL_FOO', $input);
        $value = $mock->getIntFromEnvironment('OTEL_FOO', 99);
        $this->assertSame(99, $value);
    }

    /*
     * @test
     * @dataProvider emptyProvider
     */
    public function environmentVariables_bool_usesDefaultWhenEmptyValue(?bool $input)
    {
        $mock = new MockWithTrait();
        $this->setEnvironmentVariable('OTEL_FOO', $input);
        $value = $mock->getBooleanFromEnvironment('OTEL_FOO', true);
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
     * @test
     * @dataProvider booleanProvider
     */
    public function environmentVariables_bool_get(string $input, bool $default, bool $expected)
    {
        $mock = new MockWithTrait();
        $this->setEnvironmentVariable('OTEL_BOOL', $input);
        $this->assertSame($expected, $mock->getBooleanFromEnvironment('OTEL_BOOL', $default));
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
