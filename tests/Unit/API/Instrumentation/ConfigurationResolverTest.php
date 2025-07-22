<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Instrumentation;

use OpenTelemetry\API\Instrumentation\ConfigurationResolver;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigurationResolver::class)]
class ConfigurationResolverTest extends TestCase
{
    use TestState;

    private ConfigurationResolver $resolver;

    #[\Override]
    public function setUp(): void
    {
        $this->resolver = new ConfigurationResolver();
    }

    #[DataProvider('hasProvider')]
    public function test_has(?string $value, bool $expected): void
    {
        $this->assertFalse($this->resolver->has('OTEL_FOO'));
        $this->setEnvironmentVariable('OTEL_FOO', $value);
        $this->assertSame($expected, $this->resolver->has('OTEL_FOO'));
    }

    public static function hasProvider(): array
    {
        return [
            ['bar', true],
            ['', false],
            [null, false],
        ];
    }

    public function test_get_string(): void
    {
        $this->assertFalse($this->resolver->has('OTEL_FOO'));
        $this->setEnvironmentVariable('OTEL_FOO', 'bar');
        $this->assertSame('bar', $this->resolver->getString('OTEL_FOO'));
    }

    #[DataProvider('booleanProvider')]
    public function test_get_boolean(?string $value, ?bool $expected): void
    {
        $this->assertFalse($this->resolver->has('OTEL_FOO'));
        $this->setEnvironmentVariable('OTEL_FOO', $value);
        $this->assertSame($expected, $this->resolver->getBoolean('OTEL_FOO'));
    }

    public static function booleanProvider(): array
    {
        return [
            ['true', true],
            ['false', false],
            ['random', false],
            ['', null],
            [null, null],
        ];
    }

    #[DataProvider('intProvider')]
    public function test_get_int(?string $value, ?int $expected): void
    {
        $this->assertFalse($this->resolver->has('OTEL_FOO'));
        $this->setEnvironmentVariable('OTEL_FOO', $value);
        $this->assertSame($expected, $this->resolver->getInt('OTEL_FOO'));
    }

    public static function intProvider(): array
    {
        return [
            ['0', 0],
            ['1', 1],
            ['-1', -1],
            ['', null],
            [null, null],
            ['3.14159', null],
        ];
    }

    #[DataProvider('listProvider')]
    public function test_get_list(?string $value, array $expected): void
    {
        $this->assertFalse($this->resolver->has('OTEL_FOO'));
        $this->setEnvironmentVariable('OTEL_FOO', $value);
        $this->assertSame($expected, $this->resolver->getList('OTEL_FOO'));
    }

    public static function listProvider(): array
    {
        return [
            ['foo,bar,baz', ['foo','bar','baz']],
            ['foo', ['foo']],
            ['', []],
            [null, []],
        ];
    }

    public function test_has_when_missing(): void
    {
        $this->assertFalse($this->resolver->has('OTEL_MISSING'));
    }
}
