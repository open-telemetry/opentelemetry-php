<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Propagation;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Propagation\LateBindingTextMapPropagator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LateBindingTextMapPropagator::class)]
class LateBindingTextMapPropagatorTest extends TestCase
{
    public function test_fields_delegates_to_propagator(): void
    {
        $inner = $this->createMock(TextMapPropagatorInterface::class);
        $inner->expects($this->once())->method('fields')->willReturn(['traceparent']);
        $propagator = new LateBindingTextMapPropagator($inner);
        $this->assertSame(['traceparent'], $propagator->fields());
    }

    public function test_fields_resolves_closure(): void
    {
        $inner = $this->createMock(TextMapPropagatorInterface::class);
        $inner->expects($this->once())->method('fields')->willReturn(['traceparent']);
        $propagator = new LateBindingTextMapPropagator(fn () => $inner);
        $this->assertSame(['traceparent'], $propagator->fields());
    }

    public function test_inject_delegates_to_propagator(): void
    {
        $inner = $this->createMock(TextMapPropagatorInterface::class);
        $inner->expects($this->once())->method('inject');
        $propagator = new LateBindingTextMapPropagator($inner);
        $carrier = [];
        $propagator->inject($carrier);
    }

    public function test_inject_resolves_closure(): void
    {
        $inner = $this->createMock(TextMapPropagatorInterface::class);
        $inner->expects($this->once())->method('inject');
        $propagator = new LateBindingTextMapPropagator(fn () => $inner);
        $carrier = [];
        $propagator->inject($carrier);
    }

    public function test_extract_delegates_to_propagator(): void
    {
        $context = Context::getRoot();
        $inner = $this->createMock(TextMapPropagatorInterface::class);
        $inner->expects($this->once())->method('extract')->willReturn($context);
        $propagator = new LateBindingTextMapPropagator($inner);
        $this->assertSame($context, $propagator->extract([]));
    }

    public function test_extract_resolves_closure(): void
    {
        $context = Context::getRoot();
        $inner = $this->createMock(TextMapPropagatorInterface::class);
        $inner->expects($this->once())->method('extract')->willReturn($context);
        $propagator = new LateBindingTextMapPropagator(fn () => $inner);
        $this->assertSame($context, $propagator->extract([]));
    }

    public function test_closure_called_only_once(): void
    {
        $callCount = 0;
        $inner = $this->createMock(TextMapPropagatorInterface::class);
        $inner->method('fields')->willReturn([]);
        $inner->method('extract')->willReturn(Context::getRoot());
        $propagator = new LateBindingTextMapPropagator(function () use ($inner, &$callCount) {
            $callCount++;

            return $inner;
        });
        $propagator->fields();
        $propagator->extract([]);
        $carrier = [];
        $propagator->inject($carrier);
        $this->assertSame(1, $callCount);
    }
}
