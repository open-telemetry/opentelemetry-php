<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Configuration\Resolver;

use OpenTelemetry\API\Configuration\Resolver\CompositeResolver;
use OpenTelemetry\API\Configuration\Resolver\ResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Configuration\Resolver\CompositeResolver
 * @psalm-suppress UndefinedInterfaceMethod
 * @psalm-suppress InternalClass
 * @psalm-suppress InternalMethod
 */
class CompositeResolverTest extends TestCase
{
    /** @var ResolverInterface&MockObject $one */
    private ResolverInterface $one;
    /** @var ResolverInterface&MockObject $two */
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
}
