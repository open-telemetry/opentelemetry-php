<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Trace;

use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\Context\Context;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Span::class)]
class SpanTest extends TestCase
{
    public function test_get_invalid_returns_non_recording_span(): void
    {
        $span = Span::getInvalid();
        $this->assertInstanceOf(NonRecordingSpan::class, $span);
        $this->assertFalse($span->getContext()->isValid());
    }

    public function test_get_invalid_returns_same_instance(): void
    {
        $this->assertSame(Span::getInvalid(), Span::getInvalid());
    }

    public function test_wrap_valid_context_returns_non_recording_span(): void
    {
        $context = SpanContext::create('0af7651916cd43dd8448eb211c80319c', 'b7ad6b7169203331');
        $span = Span::wrap($context);
        $this->assertInstanceOf(NonRecordingSpan::class, $span);
        $this->assertTrue($span->getContext()->isValid());
    }

    public function test_wrap_invalid_context_returns_invalid_span(): void
    {
        $span = Span::wrap(SpanContext::getInvalid());
        $this->assertSame(Span::getInvalid(), $span);
    }

    public function test_from_context_returns_span(): void
    {
        $spanContext = SpanContext::create('0af7651916cd43dd8448eb211c80319c', 'b7ad6b7169203331');
        $span = Span::wrap($spanContext);
        $context = $span->storeInContext(Context::getRoot());
        $this->assertSame($span, Span::fromContext($context));
    }

    public function test_from_context_returns_invalid_when_no_span(): void
    {
        $span = Span::fromContext(Context::getRoot());
        $this->assertSame(Span::getInvalid(), $span);
    }

    public function test_get_current_returns_span(): void
    {
        $span = Span::getCurrent();
        $this->assertInstanceOf(SpanInterface::class, $span);
    }
}
