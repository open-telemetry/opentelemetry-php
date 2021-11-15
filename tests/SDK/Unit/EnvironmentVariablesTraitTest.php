<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Exception;
use OpenTelemetry\SDK\EnvironmentVariablesTrait;
use PHPUnit\Framework\TestCase;

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
        $mock = $this->getMockForTrait(EnvironmentVariablesTrait::class);
        $this->setEnvironmentVariable('OTEL_FOO', '100');
        $value = $mock->getIntFromEnvironment('OTEL_FOO', 999); /* @phpstan-ignore-line */
        $this->assertSame(100, $value);
    }

    /**
     * @test
     */
    public function environmentVariables_integer_failure()
    {
        $mock = $this->getMockForTrait(EnvironmentVariablesTrait::class);
        $this->setEnvironmentVariable('OTEL_FOO', 'foo');
        $this->expectException(Exception::class);
        $mock->getIntFromEnvironment('OTEL_FOO', 99); /* @phpstan-ignore-line */
    }

    /**
     * @test
     */
    public function environmentVariables_integer_usesDefaultIfEnvVarNotDefined()
    {
        $mock = $this->getMockForTrait(EnvironmentVariablesTrait::class);
        $this->assertSame(20, $mock->getIntFromEnvironment('OTEL_FOO', 20)); /* @phpstan-ignore-line */
    }

    /**
     * @test
     */
    public function environmentVariables_string_get()
    {
        $mock = $this->getMockForTrait(EnvironmentVariablesTrait::class);
        $this->setEnvironmentVariable('OTEL_FOO', 'foo');
        $value = $mock->getStringFromEnvironment('OTEL_FOO', 'bar'); /* @phpstan-ignore-line */
        $this->assertSame('foo', $value);
    }

    /**
     * @test
     * @dataProvider stringProvider
     */
    public function environmentVariables_string_usesDefault(?string $input)
    {
        $mock = $this->getMockForTrait(EnvironmentVariablesTrait::class);
        $this->setEnvironmentVariable('OTEL_FOO', $input);
        $value = $mock->getStringFromEnvironment('OTEL_FOO', 'bar'); /* @phpstan-ignore-line */
        $this->assertSame('bar', $value);
    }

    public function stringProvider()
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
        $mock = $this->getMockForTrait(EnvironmentVariablesTrait::class);
        $this->setEnvironmentVariable('OTEL_BOOL', $input);
        $this->assertSame($expected, $mock->getBooleanFromEnvironment('OTEL_BOOL', $default)); /* @phpstan-ignore-line */
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
