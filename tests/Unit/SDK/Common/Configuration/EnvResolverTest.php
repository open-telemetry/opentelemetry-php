<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Configuration;

use OpenTelemetry\SDK\Common\Configuration\EnvResolver;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnvResolver::class)]
class EnvResolverTest extends TestCase
{
    use TestState;

    private EnvResolver $resolver;

    #[\Override]
    protected function setUp(): void
    {
        $this->resolver = new EnvResolver();
    }

    public function test_string_returns_null_when_not_set(): void
    {
        $this->assertNull($this->resolver->string('NONEXISTENT_VAR_12345'));
    }

    public function test_string_returns_value(): void
    {
        $this->setEnvironmentVariable('TEST_STRING_VAR', 'hello');
        $this->assertSame('hello', $this->resolver->string('TEST_STRING_VAR'));
    }

    public function test_bool_returns_null_when_not_set(): void
    {
        $this->assertNull($this->resolver->bool('NONEXISTENT_VAR_12345'));
    }

    public function test_bool_returns_value(): void
    {
        $this->setEnvironmentVariable('TEST_BOOL_VAR', 'true');
        $this->assertTrue($this->resolver->bool('TEST_BOOL_VAR'));
    }

    public function test_int_returns_null_when_not_set(): void
    {
        $this->assertNull($this->resolver->int('NONEXISTENT_VAR_12345'));
    }

    public function test_int_returns_value(): void
    {
        $this->setEnvironmentVariable('TEST_INT_VAR', '42');
        $this->assertSame(42, $this->resolver->int('TEST_INT_VAR'));
    }

    public function test_int_returns_null_when_out_of_range(): void
    {
        $this->setEnvironmentVariable('TEST_INT_VAR', '100');
        $this->assertNull($this->resolver->int('TEST_INT_VAR', 0, 50));
    }

    public function test_numeric_returns_null_when_not_set(): void
    {
        $this->assertNull($this->resolver->numeric('NONEXISTENT_VAR_12345'));
    }

    public function test_numeric_returns_value(): void
    {
        $this->setEnvironmentVariable('TEST_NUMERIC_VAR', '3.14');
        $this->assertSame(3.14, $this->resolver->numeric('TEST_NUMERIC_VAR'));
    }

    public function test_numeric_returns_null_when_out_of_range(): void
    {
        $this->setEnvironmentVariable('TEST_NUMERIC_VAR', '100.5');
        $this->assertNull($this->resolver->numeric('TEST_NUMERIC_VAR', 0, 50));
    }

    public function test_list_returns_null_when_not_set(): void
    {
        $this->assertNull($this->resolver->list('NONEXISTENT_VAR_12345'));
    }

    public function test_list_returns_value(): void
    {
        $this->setEnvironmentVariable('TEST_LIST_VAR', 'a,b,c');
        $this->assertSame(['a', 'b', 'c'], $this->resolver->list('TEST_LIST_VAR'));
    }

    public function test_map_returns_null_when_not_set(): void
    {
        $this->assertNull($this->resolver->map('NONEXISTENT_VAR_12345'));
    }

    public function test_map_returns_value(): void
    {
        $this->setEnvironmentVariable('TEST_MAP_VAR', 'key1=val1,key2=val2');
        $result = $this->resolver->map('TEST_MAP_VAR');
        $this->assertSame('val1', $result['key1']);
        $this->assertSame('val2', $result['key2']);
    }

    public function test_enum_returns_null_when_not_set(): void
    {
        $this->assertNull($this->resolver->enum('NONEXISTENT_VAR_12345', ['a', 'b']));
    }

    public function test_enum_returns_value_when_valid(): void
    {
        $this->setEnvironmentVariable('TEST_ENUM_VAR', 'a');
        $this->assertSame('a', $this->resolver->enum('TEST_ENUM_VAR', ['a', 'b']));
    }

    public function test_enum_returns_null_when_invalid(): void
    {
        $this->setEnvironmentVariable('TEST_ENUM_VAR', 'c');
        $this->assertNull($this->resolver->enum('TEST_ENUM_VAR', ['a', 'b']));
    }
}
