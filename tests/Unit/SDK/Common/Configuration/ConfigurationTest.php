<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Configuration;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Exception;
use Generator;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Configuration\VariableTypes;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @covers \OpenTelemetry\SDK\Common\Configuration\Configuration
 */
class ConfigurationTest extends TestCase
{
    use EnvironmentVariables;

    private const ALLOW_EMPTY = [
        VariableTypes::LIST,
        VariableTypes::MAP,
        VariableTypes::MIXED,
    ];

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

    private const TYPES = [
        VariableTypes::STRING => [Variables::OTEL_SERVICE_NAME],
        VariableTypes::BOOL => [Variables::OTEL_EXPORTER_OTLP_INSECURE],
        VariableTypes::INTEGER => [Variables::OTEL_EXPORTER_JAEGER_AGENT_PORT],
        VariableTypes::ENUM => [Variables::OTEL_LOG_LEVEL],
        VariableTypes::LIST => [Variables::OTEL_PROPAGATORS],
        VariableTypes::MAP => [Variables::OTEL_RESOURCE_ATTRIBUTES],
        VariableTypes::MIXED => [Variables::OTEL_TRACES_SAMPLER_ARG],
    ];

    private const USER_VALUES = [
        VariableTypes::STRING => ['foo', 'foo'],
        VariableTypes::BOOL => ['true', true],
        VariableTypes::INTEGER => ['42', 42],
        VariableTypes::ENUM => ['val1', 'val1'],
        VariableTypes::LIST => ['val1,val2', ['val1','val2']],
        VariableTypes::MAP => ['var1=val1,var2=val2', ['var1'=>'val1','var2'=>'val2']],
        VariableTypes::MIXED => ['foo', 'foo'],
    ];

    private const LIBRARY_DEFAULTS = [
        VariableTypes::STRING => [Variables::OTEL_EXPORTER_OTLP_ENDPOINT, 'http://localhost:4318'],
        VariableTypes::BOOL => [Variables::OTEL_EXPORTER_OTLP_INSECURE, false],
        VariableTypes::INTEGER => [Variables::OTEL_EXPORTER_JAEGER_AGENT_PORT, 6831],
        VariableTypes::ENUM => [Variables::OTEL_LOG_LEVEL, 'info'],
        VariableTypes::LIST => [Variables::OTEL_PROPAGATORS, ['tracecontext', 'baggage']],
    ];

    private const NO_DEFAULTS = [
        VariableTypes::STRING => [Variables::OTEL_SERVICE_NAME],
        VariableTypes::ENUM => [Variables::OTEL_EXPORTER_OTLP_COMPRESSION],
        VariableTypes::MAP => [Variables::OTEL_EXPORTER_OTLP_HEADERS],
        VariableTypes::MIXED => [Variables::OTEL_TRACES_SAMPLER_ARG],
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
    public function test_bool_get(string $input, bool $default, bool $expected)
    {
        $this->setEnvironmentVariable('OTEL_BOOL', $input);
        $this->assertSame($expected, Configuration::getBoolean('OTEL_BOOL', $default));
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

    /**
     * @dataProvider userValueProvider
     */
    public function test_return_user_env_vars(string $methodName, string $variable, string $value, $result)
    {
        $this->setEnvironmentVariable($variable, $value);

        $this->assertSame(
            $result,
            call_user_func([Configuration::class, $methodName], $variable)
        );
    }

    /**
     * @dataProvider userValueProvider
     */
    public function test_return_user_default_value(string $methodName, string $variable, string $defaultValue, $result)
    {
        $this->assertSame(
            $result,
            call_user_func([Configuration::class, $methodName], $variable, $defaultValue)
        );
    }

    /**
     * @dataProvider libraryDefaultValueProvider
     */
    public function test_return_library_default_value(string $methodName, string $variable, $result)
    {
        $this->assertSame(
            $result,
            call_user_func([Configuration::class, $methodName], $variable)
        );
    }

    /**
     * @dataProvider nonEmptyMethodNameProvider
     */
    public function test_no_value_throws_exception(string $methodName)
    {
        $this->expectException(UnexpectedValueException::class);

        call_user_func([Configuration::class, $methodName], 'FOO_BAR_' . $methodName);
    }

    /**
     * @dataProvider invalidTypeProvider
     */
    public function test_invalid_type_throws_exception(string $methodName, string $variable)
    {
        $this->expectException(UnexpectedValueException::class);

        call_user_func([Configuration::class, $methodName], $variable);
    }

    /**
     * @dataProvider noDefaultProvider
     */
    public function test_null_result_throws_exception(string $methodName, string $variable)
    {
        $this->expectException(UnexpectedValueException::class);

        call_user_func([Configuration::class, $methodName], $variable);
    }

    public function userValueProvider(): Generator
    {
        foreach (self::USER_VALUES as $varType => $values) {
            [$default, $result] = $values;

            yield $varType => [
                self::METHOD_NAMES[$varType][0],
                self::TYPES[$varType][0],
                $default,
                $result,
            ];
        }
    }

    public function libraryDefaultValueProvider(): Generator
    {
        foreach (self::LIBRARY_DEFAULTS as $varType => $values) {
            [$variable, $result] = $values;

            yield $varType => [
                self::METHOD_NAMES[$varType][0],
                $variable,
                $result,
            ];
        }
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

    public function invalidTypeProvider(): Generator
    {
        foreach (self::METHOD_NAMES as $varType => $names) {
            if ($varType === VariableTypes::MIXED) {
                continue;
            }
            $methodName = $names[0];
            foreach (self::TYPES as $methodType => $types) {
                if ($varType === $methodType || $methodType === VariableTypes::MIXED) {
                    continue;
                }
                $variableName = $types[0];

                yield sprintf('%s - %s', $varType, $methodType) => [$methodName, $variableName];
            }
        }
    }

    public function noDefaultProvider(): Generator
    {
        foreach (self::NO_DEFAULTS as $varType => $values) {
            if (in_array($varType, self::ALLOW_EMPTY)) {
                continue;
            }

            yield $varType => [self::METHOD_NAMES[$varType][0], $values[0]];
        }
    }
}
