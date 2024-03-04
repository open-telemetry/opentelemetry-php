<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\Resolver;

use OpenTelemetry\Config\Resolver\PhpIniAccessor;
use OpenTelemetry\Config\Resolver\PhpIniResolver;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Config\Resolver\PhpIniResolver
 * @psalm-suppress UndefinedMethod
 */
class PhpIniResolverTest extends TestCase
{
    private PhpIniAccessor $accessor;
    private PhpIniResolver $resolver;

    public function setUp(): void
    {
        $this->accessor = $this->createMock(PhpIniAccessor::class);
        $this->resolver = new PhpIniResolver($this->accessor);
    }

    public function test_retrieve_array(): void
    {
        $this->accessor->expects($this->once())->method('get')->willReturn(['foo', 'bar', 'baz']);
        $this->assertSame('foo,bar,baz', $this->resolver->retrieveValue('OTEL_FOO'));
    }

    public function test_retrieve_string(): void
    {
        $this->accessor->expects($this->once())->method('get')->willReturn('foo');
        $this->assertSame('foo', $this->resolver->retrieveValue('OTEL_FOO'));
    }

    /**
     * @dataProvider hasVariableProvider
     */
    public function test_has_variable($value, bool $expected): void
    {
        $this->accessor->method('get')->willReturn($value);
        $this->assertSame($expected, $this->resolver->hasVariable('OTEL_FOO'));
    }

    public function hasVariableProvider(): array
    {
        return [
            'string' => ['foo', true],
            'array' => [['foo'], true],
            'empty string' => ['', false],
            'false' => [false, false],
            'empty array' => [[], false],
        ];
    }
}
