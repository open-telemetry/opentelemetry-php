<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Configuration;

use Exception;
use Generator;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Defaults;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Configuration\VariableTypes;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(Configuration::class)]
class ConfigurationTest extends TestCase
{
    use TestState;

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
        VariableTypes::INTEGER => [Variables::OTEL_BSP_MAX_QUEUE_SIZE],
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
        VariableTypes::LIST => [['val1', 'val2'], ['val1','val2']],
        VariableTypes::MAP => [['var1' => 'val1', 'var2' => 'val2'], ['var1'=>'val1','var2'=>'val2']],
        VariableTypes::MIXED => ['foo', 'foo'],
    ];

    private const USER_ENV_VALUES = [
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
        VariableTypes::INTEGER => [Variables::OTEL_BSP_MAX_QUEUE_SIZE, 2048],
        VariableTypes::ENUM => [Variables::OTEL_LOG_LEVEL, 'info'],
        VariableTypes::LIST => [Variables::OTEL_PROPAGATORS, ['tracecontext', 'baggage']],
    ];

    private const DEFAULT_VALUES = [
        'log level' => [Variables::OTEL_LOG_LEVEL, Defaults::OTEL_LOG_LEVEL],
        'attribute count limit' => [Variables::OTEL_ATTRIBUTE_COUNT_LIMIT, Defaults::OTEL_ATTRIBUTE_COUNT_LIMIT],
        'trace exporter' => [Variables::OTEL_PHP_TRACES_PROCESSOR, Defaults::OTEL_PHP_TRACES_PROCESSOR],
    ];

    private const NO_DEFAULTS = [
        VariableTypes::STRING => [Variables::OTEL_SERVICE_NAME],
        VariableTypes::ENUM => [Variables::OTEL_EXPORTER_OTLP_COMPRESSION],
        VariableTypes::MAP => [Variables::OTEL_EXPORTER_OTLP_HEADERS],
        VariableTypes::MIXED => [Variables::OTEL_TRACES_SAMPLER_ARG],
    ];

    private const KNOWN_VALUES = [
        'log level' => [Variables::OTEL_LOG_LEVEL, KnownValues::OTEL_LOG_LEVEL],
        'trace sampler' => [Variables::OTEL_TRACES_SAMPLER, KnownValues::OTEL_TRACES_SAMPLER],
        'trace processor' => [Variables::OTEL_PHP_TRACES_PROCESSOR, KnownValues::OTEL_PHP_TRACES_PROCESSOR],
    ];

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

    public function environment_variables_integer_uses_default_if_env_var_not_defined(): void
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
     */
    #[DataProvider('emptyProvider')]
    public function test_string_uses_default_when_empty_value(?string $input): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', $input);
        $value = Configuration::getString('OTEL_FOO', 'bar');
        $this->assertSame('bar', $value);
    }

    #[DataProvider('emptyProvider')]
    public function test_int_uses_default_when_empty_value(?string $input): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', $input);
        $value = Configuration::getInt('OTEL_FOO', 99);
        $this->assertSame(99, $value);
    }

    #[DataProvider('emptyProvider')]
    public function test_bool_uses_default_when_empty_value(?string $input): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', $input);
        $value = Configuration::getBoolean('OTEL_FOO', true);
        $this->assertTrue($value);
    }

    public static function emptyProvider(): array
    {
        return [
            'no value' => [null],
            'empty string' => [''],
        ];
    }

    #[DataProvider('booleanProvider')]
    public function test_bool_get(string $input, bool $default, bool $expected): void
    {
        $this->setEnvironmentVariable('OTEL_BOOL', $input);
        $this->assertSame($expected, Configuration::getBoolean('OTEL_BOOL', $default));
    }

    public static function booleanProvider(): array
    {
        return [
            'false' => ['false', true, false],
            'true' => ['true', false, true],
            'TRUE' => ['TRUE', false, true],
            'FALSE' => ['FALSE', true, false],
        ];
    }

    public function test_list_get(): void
    {
        $this->setEnvironmentVariable('OTEL_LIST', 'a,b,c');
        $this->assertSame(['a', 'b', 'c'], Configuration::getList('OTEL_LIST'));
    }

    public function test_map_get(): void
    {
        $this->setEnvironmentVariable('OTEL_MAP', 'a=b,c=d');
        $this->assertSame(['a'=>'b', 'c'=>'d'], Configuration::getMap('OTEL_MAP'));
    }

    public function test_enum_get(): void
    {
        $this->setEnvironmentVariable('OTEL_ENUM', 'foo');
        $this->assertSame('foo', Configuration::getEnum('OTEL_ENUM'));
    }

    public function test_ratio_get(): void
    {
        $this->setEnvironmentVariable('OTEL_RATIO', '0.5');
        $this->assertSame(0.5, Configuration::getRatio('OTEL_RATIO'));
    }

    #[DataProvider('userEnvValueProvider')]
    public function test_return_user_env_vars(string $methodName, string $variable, string $value, $result): void
    {
        $this->setEnvironmentVariable($variable, $value);

        $this->assertSame(
            $result,
            call_user_func([Configuration::class, $methodName], $variable)
        );
    }

    #[DataProvider('userValueProvider')]
    public function test_return_user_default_value(string $methodName, string $variable, $defaultValue, $result): void
    {
        $this->assertSame(
            $result,
            call_user_func([Configuration::class, $methodName], $variable, $defaultValue)
        );
    }

    #[DataProvider('libraryDefaultValueProvider')]
    public function test_return_library_default_value(string $methodName, string $variable, $result): void
    {
        $this->assertSame(
            $result,
            call_user_func([Configuration::class, $methodName], $variable)
        );
    }

    #[DataProvider('nonEmptyMethodNameProvider')]
    public function test_no_value_throws_exception(string $methodName): void
    {
        $this->expectException(UnexpectedValueException::class);

        call_user_func([Configuration::class, $methodName], 'FOO_BAR_' . $methodName);
    }

    #[DataProvider('invalidTypeProvider')]
    public function test_invalid_type_throws_exception(string $methodName, string $variable): void
    {
        $this->expectException(UnexpectedValueException::class);

        call_user_func([Configuration::class, $methodName], $variable);
    }

    #[DataProvider('noDefaultProvider')]
    public function test_null_result_throws_exception(string $methodName, string $variable): void
    {
        $this->expectException(UnexpectedValueException::class);

        call_user_func([Configuration::class, $methodName], $variable);
    }

    public static function userValueProvider(): Generator
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

    public static function userEnvValueProvider(): Generator
    {
        foreach (self::USER_ENV_VALUES as $varType => $values) {
            [$default, $result] = $values;

            yield $varType => [
                self::METHOD_NAMES[$varType][0],
                self::TYPES[$varType][0],
                $default,
                $result,
            ];
        }
    }

    public static function libraryDefaultValueProvider(): Generator
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

    public static function nonEmptyMethodNameProvider(): Generator
    {
        foreach (self::METHOD_NAMES as $varType => $names) {
            if (in_array($varType, self::ALLOW_EMPTY)) {
                continue;
            }

            yield $varType => $names;
        }
    }

    public static function invalidTypeProvider(): Generator
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

    public static function noDefaultProvider(): Generator
    {
        foreach (self::NO_DEFAULTS as $varType => $values) {
            if (in_array($varType, self::ALLOW_EMPTY)) {
                continue;
            }

            yield $varType => [self::METHOD_NAMES[$varType][0], $values[0]];
        }
    }

    public function test_default_ratio_for_non_existent_variable(): void
    {
        $value = Configuration::getRatio('not-set', 0);
        $this->assertSame(0.0, $value);
    }

    #[DataProvider('knownValuesProvider')]
    public function test_get_known_values(string $varName, array $varValue): void
    {
        $this->assertSame(
            $varValue,
            Configuration::getKnownValues($varName)
        );
    }

    public static function knownValuesProvider(): array
    {
        return self::KNOWN_VALUES;
    }

    public function test_retrieve_value_library_default(): void
    {
        $value = 'simple';
        $variable = Variables::OTEL_PHP_TRACES_PROCESSOR;

        $this->assertFalse(
            Configuration::has($variable)
        );

        $this->assertSame(
            [$value],
            Configuration::getList($variable, [$value])
        );
    }

    #[DataProvider('typeProvider')]
    public function test_get_type(string $varName, string $type): void
    {
        $this->assertSame(
            $type,
            Configuration::getType($varName)
        );
    }

    public static function typeProvider(): array
    {
        return [
            'bool' => ['OTEL_EXPORTER_OTLP_INSECURE', VariableTypes::BOOL],
            'string' => ['OTEL_SERVICE_NAME', VariableTypes::STRING],
            'integer' => ['OTEL_BSP_MAX_QUEUE_SIZE', VariableTypes::INTEGER],
            'enum' => ['OTEL_LOG_LEVEL', VariableTypes::ENUM],
            'list' => ['OTEL_PROPAGATORS', VariableTypes::LIST],
            'map' => ['OTEL_RESOURCE_ATTRIBUTES', VariableTypes::MAP],
            'mixed' => ['OTEL_TRACES_SAMPLER_ARG', VariableTypes::MIXED],
        ];
    }

    #[DataProvider('defaultValueProvider')]
    public function test_get_default_value_with_empty_var(string $varName, $varValue): void
    {
        $this->setEnvironmentVariable($varName, '');

        $this->assertSame(
            $varValue,
            Configuration::getDefault($varName)
        );
    }

    #[DataProvider('defaultValueProvider')]
    public function test_get_default_value(string $varName, $varValue): void
    {
        $this->assertSame(
            $varValue,
            Configuration::getDefault($varName)
        );
    }

    public static function defaultValueProvider(): array
    {
        return self::DEFAULT_VALUES;
    }

    #[DataProvider('nonStringProvider')]
    public function test_get_non_string_value(string $method, $value): void
    {
        $_SERVER['OTEL_FOO'] = $value;
        $this->assertTrue(Configuration::has('OTEL_FOO'));
        $this->assertSame($value, call_user_func([Configuration::class, $method], 'OTEL_FOO'));
    }

    public static function nonStringProvider(): array
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
