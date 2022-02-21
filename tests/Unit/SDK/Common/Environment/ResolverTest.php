<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Environment;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\SDK\Common\Environment\Defaults;
use OpenTelemetry\SDK\Common\Environment\KnownValues;
use OpenTelemetry\SDK\Common\Environment\Resolver;
use OpenTelemetry\SDK\Common\Environment\VariableTypes;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Environment\Resolver
 */
class ResolverTest extends TestCase
{
    use EnvironmentVariables;

    private const RAW_VALUES = [
        'string' => ['STRING_VAR', 'foo'],
        'int' => ['INT_VAR', '42'],
        'float' => ['FLOAT_VAR', '4.2'],
        'list' => ['LIST_VAR', 'foo,bar,baz'],
        'map' => ['MAP_VAR', 'foo=bar,bar=baz'],
    ];

    private const DEFAULT_VALUES = [
        'log level' => ['OTEL_LOG_LEVEL', Defaults::OTEL_LOG_LEVEL],
        'attribute count limit' => ['OTEL_ATTRIBUTE_COUNT_LIMIT', Defaults::OTEL_ATTRIBUTE_COUNT_LIMIT],
        'trace exporter' => ['OTEL_PHP_TRACES_PROCESSOR', Defaults::OTEL_PHP_TRACES_PROCESSOR],
    ];

    private const TYPES = [
        'bool' => ['OTEL_EXPORTER_OTLP_INSECURE', VariableTypes::BOOL],
        'string' => ['OTEL_SERVICE_NAME', VariableTypes::STRING],
        'integer' => ['OTEL_EXPORTER_JAEGER_AGENT_PORT', VariableTypes::INTEGER],
        'enum' => ['OTEL_LOG_LEVEL', VariableTypes::ENUM],
        'list' => ['OTEL_PROPAGATORS', VariableTypes::LIST],
        'map' => ['OTEL_RESOURCE_ATTRIBUTES', VariableTypes::MAP],
        'mixed' => ['OTEL_TRACES_SAMPLER_ARG', VariableTypes::MIXED],
    ];

    private const KNOWN_VALUES = [
        'log level' => ['OTEL_LOG_LEVEL', KnownValues::OTEL_LOG_LEVEL],
        'trace sampler' => ['OTEL_TRACES_SAMPLER', KnownValues::OTEL_TRACES_SAMPLER],
        'trace processor' => ['OTEL_PHP_TRACES_PROCESSOR', KnownValues::OTEL_PHP_TRACES_PROCESSOR],
    ];

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function test_has_variable(): void
    {
        $this->assertFalse(
            Resolver::hasVariable('FOO_VAR')
        );

        $this->setEnvironmentVariable('FOO_VAR', 'FOO');

        $this->assertTrue(
            Resolver::hasVariable('FOO_VAR')
        );
    }

    /**
     * @dataProvider rawValueProvider
     */
    public function test_get_raw_value(string $varName, string $varValue): void
    {
        $this->setEnvironmentVariable($varName, $varValue);

        $this->assertSame(
            $varValue,
            Resolver::getRawValue($varName)
        );
    }

    public function test_get_raw_value_no_var(): void
    {
        $this->assertFalse(
            Resolver::hasVariable('FOO_VAR')
        );

        $this->assertNull(
            Resolver::getRawValue('FOO_VAR')
        );
    }

    /**
     * @dataProvider defaultValueProvider
     */
    public function test_get_default_value(string $varName, $varValue): void
    {
        $this->assertSame(
            $varValue,
            Resolver::getDefault($varName)
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
            Resolver::getDefault($varName)
        );
    }

    /**
     * @dataProvider typeProvider
     */
    public function test_get_type(string $varName, string $type): void
    {
        $this->assertSame(
            $type,
            Resolver::getType($varName)
        );
    }

    /**
     * @dataProvider knownValuesProvider
     */
    public function test_get_known_values(string $varName, array $varValue): void
    {
        $this->assertSame(
            $varValue,
            Resolver::getKnownValues($varName)
        );
    }

    public function test_resolve_value(): void
    {
        $value = 'simple';
        $variable = 'OTEL_PHP_TRACES_PROCESSOR';

        $this->assertFalse(
            Resolver::hasVariable($variable)
        );

        $this->setEnvironmentVariable($variable, $value);

        $this->assertSame(
            $value,
            Resolver::resolveValue($variable)
        );
    }

    public function test_resolve_value_library_default(): void
    {
        $value = 'simple';
        $variable = 'OTEL_PHP_TRACES_PROCESSOR';

        $this->assertFalse(
            Resolver::hasVariable($variable)
        );

        $this->assertSame(
            $value,
            Resolver::resolveValue($variable, $value)
        );
    }

    public function test_resolve_value_user_default(): void
    {
        $value = Defaults::OTEL_PHP_TRACES_PROCESSOR;
        $variable = 'OTEL_PHP_TRACES_PROCESSOR';

        $this->assertFalse(
            Resolver::hasVariable($variable)
        );

        $this->assertSame(
            $value,
            Resolver::resolveValue($variable)
        );
    }

    public function rawValueProvider(): array
    {
        return self::RAW_VALUES;
    }

    public function defaultValueProvider(): array
    {
        return self::DEFAULT_VALUES;
    }

    public function typeProvider(): array
    {
        return self::TYPES;
    }

    public function knownValuesProvider(): array
    {
        return self::KNOWN_VALUES;
    }
}
