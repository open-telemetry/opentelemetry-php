<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\SpanProcessor;
use OpenTelemetry\Sdk\Trace\Tracer;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Trace as API;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SpanBuilderTest extends TestCase
{
    private const SPAN_NAME = 'span_name';

    /** @var Tracer */
    private $tracer;

    /** @var API\SpanContext */
    private $sampledSpanContext;

    /** @var MockObject&SpanProcessor */
    private $spanProcessor;

    protected function setUp(): void
    {
        $this->spanProcessor = $this->createMock(SpanProcessor::class);
        $this->tracer = (new TracerProvider($this->spanProcessor))->getTracer('SpanBuilderTest');

        $this->sampledSpanContext = new SpanContext(
            '12345678876543211234567887654321',
            '8765432112345678',
            API\SpanContext::TRACE_FLAG_SAMPLED,
        );
    }

    public function test_addLink(): void
    {
        /** @var Span $span */
        $span = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->addLink($this->sampledSpanContext)
            ->addLink($this->sampledSpanContext, new Attributes())
            ->startSpan();

        $this->assertSame(2, $span->toSpanData()->getLinks()->count());
        $span->end();
    }

    public function test_addLink_invalid(): void
    {
        /** @var Span $span */
        $span = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->addLink(Span::getInvalid()->getContext())
            ->addLink(Span::getInvalid()->getContext(), new Attributes())
            ->startSpan();

        $this->assertSame(0, $span->toSpanData()->getLinks()->count());
        $span->end();
    }

    // TODO: Test truncating links & link attributes

    public function test_addLink_noEffectAfterStartSpan(): void
    {
        $spanBuilder = $this->tracer->spanBuilder(self::SPAN_NAME);

        /** @var Span $span */
        $span = $spanBuilder
            ->addLink($this->sampledSpanContext)
            ->startSpan();

        $this->assertSame(1, $span->toSpanData()->getLinks()->count());

        $spanBuilder
            ->addLink(
                new SpanContext(
                    '00000000000004d20000000000001a85',
                    '0000000000002694',
                    API\SpanContext::TRACE_FLAG_SAMPLED
                )
            );

        $this->assertSame(1, $span->toSpanData()->getLinks()->count());

        $span->end();
    }

    public function test_setAttribute(): void
    {
        /** @var Span $span */
        $span = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->setAttribute('foo', 'bar')
            ->setAttribute('bar', 123)
            ->startSpan();

        $attributes = $span->toSpanData()->getAttributes();
        $this->assertSame(2, $attributes->count());
        $this->assertSame('bar', $attributes->get('foo'));
        $this->assertSame(123, $attributes->get('bar'));

        $span->end();
    }

    public function test_setAttribute_afterEnd(): void
    {
        /** @var Span $span */
        $span = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->setAttribute('foo', 'bar')
            ->setAttribute('bar', 123)
            ->startSpan();

        $attributes = $span->toSpanData()->getAttributes();
        $this->assertSame(2, $attributes->count());
        $this->assertSame('bar', $attributes->get('foo'));
        $this->assertSame(123, $attributes->get('bar'));

        $span->end();
    }
}
