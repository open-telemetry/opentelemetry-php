<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\Configuration;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Exception;
use Generator;
use OpenTelemetry\Config\Configuration\Configuration;
use OpenTelemetry\Config\Configuration\Defaults;
use OpenTelemetry\Config\Configuration\KnownValues;
use OpenTelemetry\Config\Configuration\Variables;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @covers \OpenTelemetry\Config\Configuration\Configuration
 */
class ConfigurationTest extends TestCase
{
    use EnvironmentVariables;

    private const ALLOW_EMPTY = [
        'list',
        'map',
        'mixed',
    ];

    private const METHOD_NAMES = [
        'string' => ['getString'],
        'bool' => ['getBoolean'],
        'integer' => ['getInt'],
        'float' => ['getFloat'],
        'ratio' => ['getRatio'],
        'enum' => ['getEnum'],
        'list' => ['getList'],
        'map' => ['getMap'],
        'mixed' => ['getMixed'],
    ];

    private const TYPES = [
        'string' => [Variables::OTEL_SERVICE_NAME],
        'bool' => [Variables::OTEL_EXPORTER_OTLP_INSECURE],
        'integer' => [Variables::OTEL_BSP_MAX_QUEUE_SIZE],
        'enum' => [Variables::OTEL_LOG_LEVEL],
        'list' => [Variables::OTEL_PROPAGATORS],
        'map' => [Variables::OTEL_RESOURCE_ATTRIBUTES],
        'mixed' => [Variables::OTEL_TRACES_SAMPLER_ARG],
    ];

    private const USER_VALUES = [
        'string' => ['foo', 'foo'],
        'bool' => ['true', true],
        'integer' => ['42', 42],
        'enum' => ['val1', 'val1'],
        'list' => [['val1', 'val2'], ['val1','val2']],
        'map' => [['var1' => 'val1', 'var2' => 'val2'], ['var1'=>'val1','var2'=>'val2']],
        'mixed' => ['foo', 'foo'],
    ];

    private const USER_ENV_VALUES = [
        'string' => ['foo', 'foo'],
        'bool' => ['true', true],
        'integer' => ['42', 42],
        'enum' => ['val1', 'val1'],
        'list' => ['val1,val2', ['val1','val2']],
        'map' => ['var1=val1,var2=val2', ['var1'=>'val1','var2'=>'val2']],
        'mixed' => ['foo', 'foo'],
    ];

    private const LIBRARY_DEFAULTS = [
        'string' => [Variables::OTEL_EXPORTER_OTLP_ENDPOINT, 'http://localhost:4318'],
        'bool' => [Variables::OTEL_EXPORTER_OTLP_INSECURE, false],
        'integer' => [Variables::OTEL_BSP_MAX_QUEUE_SIZE, 2048],
        'enum' => [Variables::OTEL_LOG_LEVEL, 'info'],
        'list' => [Variables::OTEL_PROPAGATORS, ['tracecontext', 'baggage']],
    ];

    private const DEFAULT_VALUES = [
        'log level' => [Variables::OTEL_LOG_LEVEL, Defaults::OTEL_LOG_LEVEL],
        'attribute count limit' => [Variables::OTEL_ATTRIBUTE_COUNT_LIMIT, Defaults::OTEL_ATTRIBUTE_COUNT_LIMIT],
        'trace exporter' => [Variables::OTEL_PHP_TRACES_PROCESSOR, Defaults::OTEL_PHP_TRACES_PROCESSOR],
    ];

    private const NO_DEFAULTS = [
        'string' => [Variables::OTEL_SERVICE_NAME],
        'enum' => [Variables::OTEL_EXPORTER_OTLP_COMPRESSION],
        'map' => [Variables::OTEL_EXPORTER_OTLP_HEADERS],
        'mixed' => [Variables::OTEL_TRACES_SAMPLER_ARG],
    ];

    private const KNOWN_VALUES = [
        'log level' => [Variables::OTEL_LOG_LEVEL, KnownValues::OTEL_LOG_LEVEL],
        'trace sampler' => [Variables::OTEL_TRACES_SAMPLER, KnownValues::OTEL_TRACES_SAMPLER],
        'trace processor' => [Variables::OTEL_PHP_TRACES_PROCESSOR, KnownValues::OTEL_PHP_TRACES_PROCESSOR],
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

    public static function emptyProvider(): array
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

    /**
     * @dataProvider userEnvValueProvider
     */
    public function test_return_user_env_vars(string $methodName, string $variable, string $value, $result): void
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
    public function test_return_user_default_value(string $methodName, string $variable, $defaultValue, $result): void
    {
        $this->assertSame(
            $result,
            call_user_func([Configuration::class, $methodName], $variable, $defaultValue)
        );
    }

    /**
     * @dataProvider libraryDefaultValueProvider
     */
    public function test_return_library_default_value(string $methodName, string $variable, $result): void
    {
        $this->assertSame(
            $result,
            call_user_func([Configuration::class, $methodName], $variable)
        );
    }

    /**
     * @dataProvider nonEmptyMethodNameProvider
     */
    public function test_no_value_throws_exception(string $methodName): void
    {
        $this->expectException(UnexpectedValueException::class);

        call_user_func([Configuration::class, $methodName], 'FOO_BAR_' . $methodName);
    }

    /**
     * @dataProvider noDefaultProvider
     */
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

    /**
     * @dataProvider knownValuesProvider
     */
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
            $value,
            Configuration::getEnum($variable, $value)
        );
    }

    /**
     * @dataProvider defaultValueProvider
     */
    public function test_get_default_value_with_empty_var(string $varName, $varValue): void
    {
        $this->setEnvironmentVariable($varName, '');

        $this->assertSame(
            $varValue,
            Configuration::getDefault($varName)
        );
    }

    /**
     * @dataProvider defaultValueProvider
     */
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

    /**
     * @dataProvider nonStringProvider
     */
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
