<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Configuration;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Generator;
use OpenTelemetry\API\Configuration\VariableTypes;
use OpenTelemetry\SDK\Common\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Defaults;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @covers \OpenTelemetry\SDK\Common\Configuration
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

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
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

    /**
     * @dataProvider noDefaultProvider
     */
    public function test_null_result_throws_exception(string $methodName, string $variable)
    {
        $this->expectException(UnexpectedValueException::class);

        call_user_func([Configuration::class, $methodName], $variable);
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

    public function defaultValueProvider(): array
    {
        return self::DEFAULT_VALUES;
    }

    /**
     * @dataProvider userValueProvider
     */
    public function test_return_user_default_value(string $methodName, string $variable, $defaultValue, $result)
    {
        $this->assertSame(
            $result,
            call_user_func([Configuration::class, $methodName], $variable, $defaultValue)
        );
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

    /**
     * @dataProvider userEnvValueProvider
     */
    public function test_return_user_env_vars(string $methodName, string $variable, string $value, $result)
    {
        $this->setEnvironmentVariable($variable, $value);

        $this->assertSame(
            $result,
            call_user_func([Configuration::class, $methodName], $variable)
        );
    }

    public function userEnvValueProvider(): Generator
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

    /**
     * @dataProvider invalidTypeProvider
     */
    public function test_invalid_type_throws_exception(string $methodName, string $variable)
    {
        $this->expectException(UnexpectedValueException::class);

        call_user_func([Configuration::class, $methodName], $variable);
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
     * @dataProvider typeProvider
     */
    public function test_get_type(string $varName, string $type): void
    {
        $this->assertSame(
            $type,
            Configuration::getType($varName)
        );
    }

    public function typeProvider(): array
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
}
