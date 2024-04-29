<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\Configuration\Resolver;

use OpenTelemetry\Config\Configuration\Defaults;
use OpenTelemetry\Config\Configuration\Resolver\CompositeResolver;
use OpenTelemetry\Config\Configuration\Resolver\ResolverInterface;
use OpenTelemetry\Config\Configuration\Variables;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Config\Configuration\Resolver\CompositeResolver
 * @psalm-suppress UndefinedInterfaceMethod
 */
class CompositeResolverTest extends TestCase
{
    private ResolverInterface&MockObject $one;
    private ResolverInterface&MockObject $two;
    private CompositeResolver $resolver;

    public function setUp(): void
    {
        $this->one = $this->createMock(ResolverInterface::class);
        $this->two = $this->createMock(ResolverInterface::class);
        $this->resolver = new CompositeResolver([$this->one, $this->two]);
    }

    public function test_get_instance(): void
    {
        $instance = CompositeResolver::instance();
        $this->assertGreaterThanOrEqual(1, count($instance->getResolvers()));
    }

    public function test_has_variable(): void
    {
        $this->one->expects($this->once())->method('hasVariable')->willReturn(false);
        $this->two->expects($this->once())->method('hasVariable')->willReturn(true);

        $this->assertTrue($this->resolver->hasVariable('OTEL_FOO'));
    }

    public function test_not_has_variable(): void
    {
        $this->one->expects($this->once())->method('hasVariable')->willReturn(false);
        $this->two->expects($this->once())->method('hasVariable')->willReturn(false);

        $this->assertFalse($this->resolver->hasVariable('OTEL_FOO'));
    }

    public function test_resolve_when_has_variable(): void
    {
        $var = 'OTEL_FOO';
        $this->one->method('hasVariable')->willReturn(false);
        $this->two->method('hasVariable')->willReturn(true);
        $this->two
            ->expects($this->once())
            ->method('retrieveValue')
            ->with($var)
            ->willReturn('foo');

        $this->assertSame('foo', $this->resolver->resolve($var));
    }

    public function test_resolve_uses_default_when_not_empty(): void
    {
        $this->one->method('hasVariable')->willReturn(false);
        $this->two->method('hasVariable')->willReturn(false);

        $this->assertSame('foo', $this->resolver->resolve(Variables::OTEL_EXPORTER_OTLP_PROTOCOL, 'foo'));
    }

    /**
     * @dataProvider emptyProvider
     */
    public function test_resolve_uses_library_default_when_empty(?string $value): void
    {
        $this->one->method('hasVariable')->willReturn(false);
        $this->two->method('hasVariable')->willReturn(false);

        $this->assertSame(Defaults::OTEL_EXPORTER_OTLP_PROTOCOL, $this->resolver->resolve(Variables::OTEL_EXPORTER_OTLP_PROTOCOL, $value));
    }

    public static function emptyProvider(): array
    {
        return [
            [''],
            [null],
        ];
    }
}