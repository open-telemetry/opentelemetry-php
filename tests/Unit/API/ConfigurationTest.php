<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Exception;
use Generator;
use OpenTelemetry\API\Configuration;
use OpenTelemetry\API\Configuration\VariableTypes;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @covers OpenTelemetry\API\Configuration
 */
class ConfigurationTest extends TestCase
{
    use EnvironmentVariables;

    private const METHOD_NAMES = [
        VariableTypes::STRING => ['getString'],
        VariableTypes::BOOL => ['getBoolean'],
        VariableTypes::INTEGER => ['getInt'],
        VariableTypes::FLOAT => ['getFloat'],
        VariableTypes::RATIO => ['getRatio'],
        VariableTypes::ENUM => ['getEnum'],
        VariableTypes::LIST => ['getList'],
        VariableTypes::MAP => ['getMap'],
        VariableTypes::MIXED => ['getMixed'],
    ];

    private const ALLOW_EMPTY = [
        VariableTypes::LIST,
        VariableTypes::MAP,
        VariableTypes::MIXED,
    ];

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function test_has_variable_from_environment(): void
    {
        $this->assertFalse(Configuration::has('FOO_VAR'));
        $this->setEnvironmentVariable('FOO_VAR', 'FOO');
        $this->assertTrue(Configuration::has('FOO_VAR'));
    }

    public function test_integer_get(): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', '100');
        $value = Configuration::getInt('OTEL_FOO', 999);
        $this->assertSame(100, $value);
    }

    public function test_integer_failure(): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', 'foo');
        $this->expectException(Exception::class);
        Configuration::getInt('OTEL_FOO', 99);
    }

    public function environment_variables_integer_uses_default_if_env_var_not_defined()
    {
        $this->assertSame(20, Configuration::getInt('OTEL_FOO', 20));
    }

    public function test_string_get(): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', 'foo');
        $value = Configuration::getString('OTEL_FOO', 'bar');
        $this->assertSame('foo', $value);
    }

    /**
     * The SDK MUST interpret an empty value of an environment variable the same way as when the variable is unset
     * @dataProvider emptyProvider
     */
    public function test_string_uses_default_when_empty_value(?string $input): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', $input);
        $value = Configuration::getString('OTEL_FOO', 'bar');
        $this->assertSame('bar', $value);
    }

    /**
     * @dataProvider emptyProvider
     */
    public function test_int_uses_default_when_empty_value(?string $input): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', $input);
        $value = Configuration::getInt('OTEL_FOO', 99);
        $this->assertSame(99, $value);
    }

    /**
     * @dataProvider emptyProvider
     */
    public function test_bool_uses_default_when_empty_value(?string $input): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', $input);
        $value = Configuration::getBoolean('OTEL_FOO', true);
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
    public function test_bool_get(string $input, bool $default, bool $expected): void
    {
        $this->setEnvironmentVariable('OTEL_BOOL', $input);
        $this->assertSame($expected, Configuration::getBoolean('OTEL_BOOL', $default));
    }

    public function booleanProvider(): array
    {
        return [
            'false' => ['false', true, false],
            'true' => ['true', false, true],
            'TRUE' => ['TRUE', false, true],
            'FALSE' => ['FALSE', true, false],
        ];
    }

    public function test_list_get()
    {
        $this->setEnvironmentVariable('OTEL_LIST', 'a,b,c');
        $this->assertSame(['a', 'b', 'c'], Configuration::getList('OTEL_LIST'));
    }

    public function test_map_get()
    {
        $this->setEnvironmentVariable('OTEL_MAP', 'a=b,c=d');
        $this->assertSame(['a'=>'b', 'c'=>'d'], Configuration::getMap('OTEL_MAP'));
    }

    public function test_enum_get()
    {
        $this->setEnvironmentVariable('OTEL_ENUM', 'foo');
        $this->assertSame('foo', Configuration::getEnum('OTEL_ENUM'));
    }

    public function test_ratio_get()
    {
        $this->setEnvironmentVariable('OTEL_RATIO', '0.5');
        $this->assertSame(0.5, Configuration::getRatio('OTEL_RATIO'));
    }

    public function test_get_type(): void
    {
        $this->assertSame('mixed', Configuration::getType('OTEL_FOO'));
    }

    /**
     * @dataProvider nonEmptyMethodNameProvider
     */
    public function test_no_value_throws_exception(string $methodName)
    {
        $this->expectException(UnexpectedValueException::class);

        call_user_func([Configuration::class, $methodName], 'FOO_BAR_' . $methodName);
    }

    public function nonEmptyMethodNameProvider(): Generator
    {
        foreach (self::METHOD_NAMES as $varType => $names) {
            if (in_array($varType, self::ALLOW_EMPTY)) {
                continue;
            }

            yield $varType => $names;
        }
    }

    public function test_default_ratio_for_non_existent_variable(): void
    {
        $value = Configuration::getRatio('not-set', 0);
        $this->assertSame(0.0, $value);
    }

    /**
     * @dataProvider nonStringProvider
     */
    public function test_get_non_string_value(string $method, $value): void
    {
        $_SERVER['OTEL_FOO'] = $value;
        $this->assertTrue(Configuration::has('OTEL_FOO'));
        $this->assertSame($value, call_user_func([Configuration::class, $method], 'OTEL_FOO'));
    }

    public function nonStringProvider(): array
    {
        return [
            ['getFloat', 3.14159],
            ['getInt', 22],
            ['getBoolean', true],
            ['getRatio', 0.44],
            ['getMixed', [25, 'green']],
            ['getList', ['foo', 'bar']],
            ['getMap', ['key1' => 'val1', 'key2' => 'val2']],
        ];
    }
}
