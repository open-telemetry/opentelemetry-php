<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Configuration;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\SDK\Common\Configuration\Defaults;
use OpenTelemetry\SDK\Common\Configuration\EnvironmentResolver;
use OpenTelemetry\SDK\Common\Configuration\VariableTypes;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Configuration\EnvironmentResolver
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

    private const DEFAULT_VALUES = [
        'log level' => ['OTEL_LOG_LEVEL', Defaults::OTEL_LOG_LEVEL],
        'attribute count limit' => ['OTEL_ATTRIBUTE_COUNT_LIMIT', Defaults::OTEL_ATTRIBUTE_COUNT_LIMIT],
        'trace exporter' => ['OTEL_PHP_TRACES_PROCESSOR', Defaults::OTEL_PHP_TRACES_PROCESSOR],
    ];

    private const TYPES = [
        'bool' => ['OTEL_EXPORTER_OTLP_INSECURE', VariableTypes::BOOL],
        'string' => ['OTEL_SERVICE_NAME', VariableTypes::STRING],
        'integer' => ['OTEL_EXPORTER_JAEGER_AGENT_PORT', \OpenTelemetry\SDK\Common\Configuration\VariableTypes::INTEGER],
        'enum' => ['OTEL_LOG_LEVEL', \OpenTelemetry\SDK\Common\Configuration\VariableTypes::ENUM],
        'list' => ['OTEL_PROPAGATORS', \OpenTelemetry\SDK\Common\Configuration\VariableTypes::LIST],
        'map' => ['OTEL_RESOURCE_ATTRIBUTES', VariableTypes::MAP],
        'mixed' => ['OTEL_TRACES_SAMPLER_ARG', VariableTypes::MIXED],
    ];

    private const KNOWN_VALUES = [
        'log level' => ['OTEL_LOG_LEVEL', \OpenTelemetry\SDK\Common\Configuration\KnownValues::OTEL_LOG_LEVEL],
        'trace sampler' => ['OTEL_TRACES_SAMPLER', \OpenTelemetry\SDK\Common\Configuration\KnownValues::OTEL_TRACES_SAMPLER],
        'trace processor' => ['OTEL_PHP_TRACES_PROCESSOR', \OpenTelemetry\SDK\Common\Configuration\KnownValues::OTEL_PHP_TRACES_PROCESSOR],
    ];

    /**
     * All injected environment variables.
     */
    private array $injectedEnvironmentVariables = [];

    private EnvironmentResolver $resolver;

    public function setUp(): void
    {
        $this->resolver = new EnvironmentResolver();
    }

    public function tearDown(): void
    {
        $this->resetEnvironmentVariables();
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
    public function test_get_raw_value(string $varName, string $varValue): void
    {
        $this->setEnvironmentVariable($varName, $varValue);

        $this->assertSame(
            $varValue,
            $this->resolver->getRawValue($varName)
        );
    }

    /**
     * @dataProvider rawValueProvider
     */
    public function test_get_raw_value_with_injected_value(string $varName, string $varValue): void
    {
        $this->injectEnvironmentVariable($varName, $varValue);

        $this->assertSame(
            $varValue,
            $this->resolver->getRawValue($varName)
        );
    }

    public function test_get_raw_value_no_var(): void
    {
        $this->assertFalse(
            $this->resolver->hasVariable('FOO_VAR')
        );

        $this->assertNull(
            $this->resolver->getRawValue('FOO_VAR')
        );
    }

    /**
     * @dataProvider defaultValueProvider
     */
    public function test_get_default_value(string $varName, $varValue): void
    {
        $this->assertSame(
            $varValue,
            $this->resolver->getDefault($varName)
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
            $this->resolver->getDefault($varName)
        );
    }

    /**
     * @dataProvider typeProvider
     */
    public function test_get_type(string $varName, string $type): void
    {
        $this->assertSame(
            $type,
            $this->resolver->getType($varName)
        );
    }

    /**
     * @dataProvider knownValuesProvider
     */
    public function test_get_known_values(string $varName, array $varValue): void
    {
        $this->assertSame(
            $varValue,
            $this->resolver->getKnownValues($varName)
        );
    }

    public function test_resolve_value(): void
    {
        $value = 'simple';
        $variable = 'OTEL_PHP_TRACES_PROCESSOR';

        $this->assertFalse(
            $this->resolver->hasVariable($variable)
        );

        $this->setEnvironmentVariable($variable, $value);

        $this->assertSame(
            $value,
            $this->resolver->resolveValue($variable)
        );
    }

    public function test_resolve_value_with_injected_value(): void
    {
        $value = 'simple';
        $variable = 'OTEL_PHP_TRACES_PROCESSOR';

        $this->assertFalse(
            $this->resolver->hasVariable($variable)
        );

        $this->injectEnvironmentVariable($variable, $value);

        $this->assertSame(
            $value,
            $this->resolver->resolveValue($variable)
        );
    }

    public function test_resolve_value_library_default(): void
    {
        $value = 'simple';
        $variable = 'OTEL_PHP_TRACES_PROCESSOR';

        $this->assertFalse(
            $this->resolver->hasVariable($variable)
        );

        $this->assertSame(
            $value,
            $this->resolver->resolveValue($variable, $value)
        );
    }

    public function test_resolve_value_user_default(): void
    {
        $value = Defaults::OTEL_PHP_TRACES_PROCESSOR;
        $variable = 'OTEL_PHP_TRACES_PROCESSOR';

        $this->assertFalse(
            $this->resolver->hasVariable($variable)
        );

        $this->assertSame(
            $value,
            $this->resolver->resolveValue($variable)
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

    private function injectEnvironmentVariable(string $name, string $value): void
    {
        if (!in_array($name, $this->injectedEnvironmentVariables, true)) {
            $this->injectedEnvironmentVariables[] = $name;
        }

        $_ENV[$name] = $value;
    }

    private function resetEnvironmentVariables(): void
    {
        $this->restoreEnvironmentVariables();

        foreach ($this->injectedEnvironmentVariables as $variable) {
            unset($_ENV[$variable]);
        }
    }
}
