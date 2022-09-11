<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Trace;

use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\NoopSpanBuilder;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKeys;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\Tests\Unit\SDK\Util\TestClock;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Trace\NoopSpanBuilder
 */
class NoopSpanBuilderTest extends TestCase
{
    public function test_set_parent(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder($contextStorage))->setParent(
            // @todo: Create a interface for Context to allow it to be mocked
                Context::getRoot()
            )
        );
    }

    public function test_noop_created_span_uses_provided_context(): void
    {
        $spanContext = $this->createMock(SpanContextInterface::class);

        $span = $this->createMock(SpanInterface::class);
        $span->method('getContext')->willReturn($spanContext);

        $context = Context::getRoot()->with(ContextKeys::span(), $span);

        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $span = (new NoopSpanBuilder($contextStorage))
            ->setParent($context)
            ->startSpan()
        ;

        $this->assertSame($span->getContext(), $spanContext);
    }

    public function test_noop_created_span_uses_current_context(): void
    {
        $spanContext = $this->createMock(SpanContextInterface::class);

        $span = $this->createMock(SpanInterface::class);
        $span->method('getContext')->willReturn($spanContext);

        $context = Context::getRoot()->with(ContextKeys::span(), $span);

        $contextStorage = $this->createMock(ContextStorageInterface::class);
        $contextStorage->method('current')->willReturn($context);

        $span = (new NoopSpanBuilder($contextStorage))
            ->startSpan()
        ;

        $this->assertSame($span->getContext(), $spanContext);
    }

    public function test_noop_created_span_doesnt_use_current_context_if_no_parent(): void
    {
        $spanContext = $this->createMock(SpanContextInterface::class);

        $span = $this->createMock(SpanInterface::class);
        $span->method('getContext')->willReturn($spanContext);

        $context = Context::getRoot()->with(ContextKeys::span(), $span);

        $contextStorage = $this->createMock(ContextStorageInterface::class);
        $contextStorage->method('current')->willReturn($context);

        $span = (new NoopSpanBuilder($contextStorage))
            ->setNoParent()
            ->startSpan()
        ;

        $this->assertNotSame($span->getContext(), $spanContext);
        $this->assertFalse($span->getContext()->isValid());
    }

    public function test_noop_created_span_removes_is_recording_flag(): void
    {
        $span = $this->createMock(SpanInterface::class);
        $span->method('isRecording')->willReturn(true);

        $context = Context::getRoot()->with(ContextKeys::span(), $span);

        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $span = (new NoopSpanBuilder($contextStorage))
            ->setParent($context)
            ->startSpan()
        ;

        $this->assertFalse($span->isRecording());
    }

    public function test_set_no_parent(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder($contextStorage))->setNoParent()
        );
    }

    public function test_add_link(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder($contextStorage))->addLink(
                $this->createMock(SpanContextInterface::class)
            )
        );
    }

    public function test_set_attribute(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder($contextStorage))->setAttribute('foo', 'bar')
        );
    }

    public function test_set_attributes(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder($contextStorage))->setAttributes([])
        );
    }

    public function test_set_start_timestamp(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder($contextStorage))->setStartTimestamp(
                (new TestClock())->now()
            )
        );
    }

    public function test_set_span_kind(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder($contextStorage))->setSpanKind(1)
        );
    }

    public function test_start_span(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);
        $contextStorage->method('current')->willReturn(Context::getRoot());

        $this->assertInstanceOf(
            NonRecordingSpan::class,
            (new NoopSpanBuilder($contextStorage))->startSpan()
        );
    }
}
