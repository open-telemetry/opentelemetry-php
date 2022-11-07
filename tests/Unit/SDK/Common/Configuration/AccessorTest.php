<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Configuration;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Generator;
use OpenTelemetry\SDK\Common\Configuration\Accessor;
use OpenTelemetry\SDK\Common\Configuration\Variables as Env;
use OpenTelemetry\SDK\Common\Configuration\VariableTypes;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @covers \OpenTelemetry\SDK\Common\Configuration\Accessor
 */
class AccessorTest extends TestCase
{
    use EnvironmentVariables;

    private const ALLOW_EMPTY = [
        VariableTypes::LIST,
        VariableTypes::MAP,
        VariableTypes::MIXED,
    ];

    private const METHOD_NAMES = [
        VariableTypes::STRING => ['getString'],
        VariableTypes::BOOL => ['getBool'],
        VariableTypes::INTEGER => ['getInt'],
        VariableTypes::FLOAT => ['getFloat'],
        VariableTypes::RATIO => ['getRatio'],
        VariableTypes::ENUM => ['getEnum'],
        VariableTypes::LIST => ['getList'],
        VariableTypes::MAP => ['getMap'],
        VariableTypes::MIXED => ['getMixed'],
    ];

    private const TYPES = [
        VariableTypes::STRING => [Env::OTEL_SERVICE_NAME],
        VariableTypes::BOOL => [Env::OTEL_EXPORTER_OTLP_INSECURE],
        VariableTypes::INTEGER => [Env::OTEL_EXPORTER_JAEGER_AGENT_PORT],
        VariableTypes::ENUM => [Env::OTEL_LOG_LEVEL],
        VariableTypes::LIST => [Env::OTEL_PROPAGATORS],
        VariableTypes::MAP => [Env::OTEL_RESOURCE_ATTRIBUTES],
        VariableTypes::MIXED => [Env::OTEL_TRACES_SAMPLER_ARG],
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
        VariableTypes::STRING => [Env::OTEL_EXPORTER_OTLP_ENDPOINT, 'http://localhost:4318'],
        VariableTypes::BOOL => [Env::OTEL_EXPORTER_OTLP_INSECURE, false],
        VariableTypes::INTEGER => [Env::OTEL_EXPORTER_JAEGER_AGENT_PORT, 6831],
        VariableTypes::ENUM => [Env::OTEL_LOG_LEVEL, 'info'],
        VariableTypes::LIST => [Env::OTEL_PROPAGATORS, ['tracecontext', 'baggage']],
    ];

    private const NO_DEFAULTS = [
        VariableTypes::STRING => [Env::OTEL_SERVICE_NAME],
        VariableTypes::ENUM => [Env::OTEL_EXPORTER_OTLP_COMPRESSION],
        VariableTypes::MAP => [Env::OTEL_EXPORTER_OTLP_HEADERS],
        VariableTypes::MIXED => [Env::OTEL_TRACES_SAMPLER_ARG],
    ];

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @dataProvider userValueProvider
     */
    public function test_return_user_env_vars(string $methodName, string $variable, string $value, $result)
    {
        $this->setEnvironmentVariable($variable, $value);

        $this->assertSame(
            $result,
            call_user_func([\OpenTelemetry\SDK\Common\Configuration\Accessor::class, $methodName], $variable)
        );
    }

    /**
     * @dataProvider userValueProvider
     */
    public function test_return_user_default_value(string $methodName, string $variable, string $defaultValue, $result)
    {
        $this->assertSame(
            $result,
            call_user_func([\OpenTelemetry\SDK\Common\Configuration\Accessor::class, $methodName], $variable, $defaultValue)
        );
    }

    /**
     * @dataProvider libraryDefaultValueProvider
     */
    public function test_return_library_default_value(string $methodName, string $variable, $result)
    {
        $this->assertSame(
            $result,
            call_user_func([Accessor::class, $methodName], $variable)
        );
    }

    /**
     * @dataProvider nonEmptyMethodNameProvider
     */
    public function test_no_value_throws_exception(string $methodName)
    {
        $this->expectException(UnexpectedValueException::class);

        call_user_func([\OpenTelemetry\SDK\Common\Configuration\Accessor::class, $methodName], 'FOO_BAR_' . $methodName);
    }

    /**
     * @dataProvider invalidTypeProvider
     */
    public function test_invalid_type_throws_exception(string $methodName, string $variable)
    {
        $this->expectException(UnexpectedValueException::class);

        call_user_func([\OpenTelemetry\SDK\Common\Configuration\Accessor::class, $methodName], $variable);
    }

    /**
     * @dataProvider noDefaultProvider
     */
    public function test_null_result_throws_exception(string $methodName, string $variable)
    {
        $this->expectException(UnexpectedValueException::class);

        call_user_func([\OpenTelemetry\SDK\Common\Configuration\Accessor::class, $methodName], $variable);
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
