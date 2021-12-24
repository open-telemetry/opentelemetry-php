<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace;

use OpenTelemetry\API\AttributesInterface;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\NoopSpanBuilder;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanContextKey;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\Tests\SDK\Util\TestClock;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Trace\NoopSpanBuilder
 */
class NoopSpanBuilderTest extends TestCase
{
    public function testSetParent(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder($contextStorage))->setParent(
            // @todo: Create a interface for Context to allow it to be mocked
                new Context()
            )
        );
    }

    public function testNoopCreatedSpanUsesProvidedContext(): void
    {
        $spanContext = $this->createMock(SpanContextInterface::class);

        $span = $this->createMock(SpanInterface::class);
        $span->method('getContext')->willReturn($spanContext);

        $context = $this->createMock(Context::class);
        $context->method('get')->with(SpanContextKey::instance())->willReturn($span);

        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $span = (new NoopSpanBuilder($contextStorage))
            ->setParent($context)
            ->startSpan()
        ;

        $this->assertSame($span->getContext(), $spanContext);
    }

    public function testNoopCreatedSpanUsesCurrentContext(): void
    {
        $spanContext = $this->createMock(SpanContextInterface::class);

        $span = $this->createMock(SpanInterface::class);
        $span->method('getContext')->willReturn($spanContext);

        $context = $this->createMock(Context::class);
        $context->method('get')->with(SpanContextKey::instance())->willReturn($span);

        $contextStorage = $this->createMock(ContextStorageInterface::class);
        $contextStorage->method('current')->willReturn($context);

        $span = (new NoopSpanBuilder($contextStorage))
            ->startSpan()
        ;

        $this->assertSame($span->getContext(), $spanContext);
    }

    public function testNoopCreatedSpanDoesntUseCurrentContextIfNoParent(): void
    {
        $spanContext = $this->createMock(SpanContextInterface::class);

        $span = $this->createMock(SpanInterface::class);
        $span->method('getContext')->willReturn($spanContext);

        $context = $this->createMock(Context::class);
        $context->method('get')->with(SpanContextKey::instance())->willReturn($span);

        $contextStorage = $this->createMock(ContextStorageInterface::class);
        $contextStorage->method('current')->willReturn($context);

        $span = (new NoopSpanBuilder($contextStorage))
            ->setNoParent()
            ->startSpan()
        ;

        $this->assertNotSame($span->getContext(), $spanContext);
        $this->assertFalse($span->getContext()->isValid());
    }

    public function testNoopCreatedSpanRemovesIsRecordingFlag(): void
    {
        $span = $this->createMock(SpanInterface::class);
        $span->method('isRecording')->willReturn(true);

        $context = $this->createMock(Context::class);
        $context->method('get')->with(SpanContextKey::instance())->willReturn($span);

        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $span = (new NoopSpanBuilder($contextStorage))
            ->setParent($context)
            ->startSpan()
        ;

        $this->assertFalse($span->isRecording());
    }

    public function testSetNoParent(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder($contextStorage))->setNoParent()
        );
    }

    public function testAddLink(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder($contextStorage))->addLink(
                $this->createMock(SpanContextInterface::class)
            )
        );
    }

    public function testSetAttribute(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder($contextStorage))->setAttribute('foo', 'bar')
        );
    }

    public function testSetAttributes(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder($contextStorage))->setAttributes(
                $this->createMock(AttributesInterface::class)
            )
        );
    }

    public function testSetStartTimestamp(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder($contextStorage))->setStartTimestamp(
                (new TestClock())->now()
            )
        );
    }

    public function testSetSpanKind(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder($contextStorage))->setSpanKind(1)
        );
    }

    public function testStartSpan(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $this->assertInstanceOf(
            NonRecordingSpan::class,
            (new NoopSpanBuilder($contextStorage))->startSpan()
        );
    }
}
