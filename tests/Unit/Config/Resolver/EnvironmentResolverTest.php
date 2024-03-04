<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\Resolver;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\Config\Resolver\EnvironmentResolver;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Config\Resolver\EnvironmentResolver
 */
class EnvironmentResolverTest extends TestCase
{
    use EnvironmentVariables;

    private const RAW_VALUES = [
        'string' => ['STRING_VAR', 'foo'],
        'int' => ['INT_VAR', '42'],
        'float' => ['FLOAT_VAR', '4.2'],
        'list' => ['LIST_VAR', 'foo,bar,baz'],
        'map' => ['MAP_VAR', 'foo=bar,bar=baz'],
    ];

    private EnvironmentResolver $resolver;

    public function setUp(): void
    {
        $this->resolver = new EnvironmentResolver();
    }

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function test_has_variable(): void
    {
        $this->assertFalse(
            $this->resolver->hasVariable('FOO_VAR')
        );

        $this->setEnvironmentVariable('FOO_VAR', 'FOO');

        $this->assertTrue(
            $this->resolver->hasVariable('FOO_VAR')
        );
    }

    public function test_has_variable_with_injected_value(): void
    {
        $this->assertFalse(
            $this->resolver->hasVariable('FOO_VAR')
        );

        $this->injectEnvironmentVariable('FOO_VAR', 'FOO');

        $this->assertTrue(
            $this->resolver->hasVariable('FOO_VAR')
        );
    }

    /**
     * @dataProvider rawValueProvider
     */
    public function test_retrieve_value(string $varName, string $varValue): void
    {
        $this->setEnvironmentVariable($varName, $varValue);

        $this->assertSame(
            $varValue,
            $this->resolver->retrieveValue($varName)
        );
    }

    public function test_retrieve_value_no_var(): void
    {
        $this->assertFalse(
            $this->resolver->hasVariable('FOO_VAR')
        );

        $this->assertNull(
            $this->resolver->retrieveValue('FOO_VAR')
        );
    }

    public function test_retrieve_value_with_injected_value(): void
    {
        $value = 'simple';
        $variable = 'OTEL_PHP_TRACES_PROCESSOR';

        $this->assertFalse(
            $this->resolver->hasVariable($variable)
        );

        $this->injectEnvironmentVariable($variable, $value);

        $this->assertSame(
            $value,
            $this->resolver->retrieveValue($variable)
        );
    }

    public function rawValueProvider(): array
    {
        return self::RAW_VALUES;
    }

    private function injectEnvironmentVariable(string $name, $value): void
    {
        $_SERVER[$name] = $value;
    }

    public function test_get_array_from_env(): void
    {
        $this->injectEnvironmentVariable('OTEL_FOO', ['foo', 'bar']);
        $value = $this->resolver->retrieveValue('OTEL_FOO');
        $this->assertEqualsCanonicalizing(['foo', 'bar'], $value);
    }
}
