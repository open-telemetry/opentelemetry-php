<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Configuration\Resolver;

use OpenTelemetry\SDK\Common\Configuration\Defaults;
use OpenTelemetry\SDK\Common\Configuration\Resolver\CompositeResolver;
use OpenTelemetry\SDK\Common\Configuration\Resolver\ResolverInterface;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Configuration\Resolver\CompositeResolver
 * @psalm-suppress UndefinedInterfaceMethod
 */
class CompositeResolverTest extends TestCase
{
    private ResolverInterface $one;
    private ResolverInterface $two;
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

    public function emptyProvider(): array
    {
        return [
            [''],
            [null],
        ];
    }
}
