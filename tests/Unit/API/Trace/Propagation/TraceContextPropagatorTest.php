<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace\Propagation;

use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\TraceState;
use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Span;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\API\Trace\Propagation\TraceContextPropagator
 */
class TraceContextPropagatorTest extends TestCase
{
    private const TRACE_ID_BASE16 = 'ff000000000000000000000000000041';
    private const SPAN_ID_BASE16 = 'ff00000000000041';
    private const TRACEPARENT_HEADER_SAMPLED = '00-' . self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-01';
    private const TRACEPARENT_HEADER_NOT_SAMPLED = '00-' . self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-00';
    private const TRACESTATE_NOT_DEFAULT_ENCODING = 'bar=baz,foo=bar';
    private const TRACESTATE_NOT_DEFAULT_ENCODING_WITH_SPACES = 'bar=baz   ,    foo=bar';

    private TraceContextPropagator $traceContextPropagator;
    private TraceStateInterface $traceState;

    protected function setUp(): void
    {
        $this->traceContextPropagator = TraceContextPropagator::getInstance();
        $this->traceState = (new TraceState())->with('bar', 'baz')->with('foo', 'bar');
    }

    public function test_fields(): void
    {
        $this->assertSame(
            ['traceparent', 'tracestate'],
            $this->traceContextPropagator->fields()
        );
    }

    public function test_inject_empty(): void
    {
        $carrier = [];
        $this->traceContextPropagator->inject($carrier);
        $this->assertEmpty($carrier);
    }

    public function test_inject_invalid_context(): void
    {
        $carrier = [];
        $this
            ->traceContextPropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(
                        SpanContext::INVALID_TRACE,
                        SpanContext::INVALID_SPAN,
                        SpanContext::SAMPLED_FLAG
                    ),
                    Context::getCurrent()
                )
            );
        $this->assertEmpty($carrier);
    }

    public function test_inject_sampled_context(): void
    {
        $carrier = [];
        $this
            ->traceContextPropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [TraceContextPropagator::TRACEPARENT => self::TRACEPARENT_HEADER_SAMPLED],
            $carrier
        );
    }

    public function test_inject_sampled_context_with_trace_state(): void
    {
        $carrier = [];
        $this
            ->traceContextPropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED, $this->traceState),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [
                TraceContextPropagator::TRACEPARENT => self::TRACEPARENT_HEADER_SAMPLED,
                TraceContextPropagator::TRACESTATE => 'foo=bar,bar=baz',
            ],
            $carrier
        );
    }

    public function test_inject_non_sampled_context(): void
    {
        $carrier = [];
        $this
            ->traceContextPropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [TraceContextPropagator::TRACEPARENT => self::TRACEPARENT_HEADER_NOT_SAMPLED],
            $carrier
        );
    }

    public function test_inject_non_sampled_context_with_trace_state(): void
    {
        $carrier = [];
        $this
            ->traceContextPropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_DEFAULT, $this->traceState),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [
                TraceContextPropagator::TRACEPARENT => self::TRACEPARENT_HEADER_NOT_SAMPLED,
                TraceContextPropagator::TRACESTATE => 'foo=bar,bar=baz',
            ],
            $carrier
        );
    }

    public function test_extract_nothing(): void
    {
        $this->assertSame(
            Context::getCurrent(),
            $this->traceContextPropagator->extract([])
        );
    }

    public function test_extract_sampled_context(): void
    {
        $carrier = [
            TraceContextPropagator::TRACEPARENT => self::TRACEPARENT_HEADER_SAMPLED,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($this->traceContextPropagator->extract($carrier))
        );
    }

    public function test_extract_sampled_context_with_trace_state(): void
    {
        $carrier = [
            TraceContextPropagator::TRACEPARENT => self::TRACEPARENT_HEADER_SAMPLED,
            TraceContextPropagator::TRACESTATE => self::TRACESTATE_NOT_DEFAULT_ENCODING,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED, $this->traceState),
            $this->getSpanContext($this->traceContextPropagator->extract($carrier))
        );
    }

    public function test_extract_non_sampled_context(): void
    {
        $carrier = [
            TraceContextPropagator::TRACEPARENT => self::TRACEPARENT_HEADER_NOT_SAMPLED,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
            $this->getSpanContext($this->traceContextPropagator->extract($carrier))
        );
    }

    public function test_extract_non_sampled_context_with_trace_state(): void
    {
        $carrier = [
            TraceContextPropagator::TRACEPARENT => self::TRACEPARENT_HEADER_NOT_SAMPLED,
            TraceContextPropagator::TRACESTATE => self::TRACESTATE_NOT_DEFAULT_ENCODING,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_DEFAULT, $this->traceState),
            $this->getSpanContext($this->traceContextPropagator->extract($carrier))
        );
    }

    public function test_extract_and_inject(): void
    {
        $traceParent = '00-' . self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-01';
        $extractCarrier = [
            TraceContextPropagator::TRACEPARENT => $traceParent,
        ];
        $context = $this->traceContextPropagator->extract($extractCarrier);
        $injectCarrier = [];
        $this->traceContextPropagator->inject($injectCarrier, null, $context);
        $this->assertSame($injectCarrier, $extractCarrier);
    }

    public function test_extract_trace_state_with_spaces(): void
    {
        $carrier = [
            TraceContextPropagator::TRACEPARENT => self::TRACEPARENT_HEADER_NOT_SAMPLED,
            TraceContextPropagator::TRACESTATE => self::TRACESTATE_NOT_DEFAULT_ENCODING_WITH_SPACES,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_DEFAULT, $this->traceState),
            $this->getSpanContext($this->traceContextPropagator->extract($carrier))
        );
    }

    public function test_extract_empty_trace_state(): void
    {
        $carrier = [
            TraceContextPropagator::TRACEPARENT => self::TRACEPARENT_HEADER_NOT_SAMPLED,
            TraceContextPropagator::TRACESTATE => '',
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_DEFAULT, new TraceState()),
            $this->getSpanContext($this->traceContextPropagator->extract($carrier))
        );
    }

    public function test_extract_empty_header(): void
    {
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => '',
        ]);
    }

    public function test_empty_trace_id(): void
    {
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => '00--' . self::SPAN_ID_BASE16 . '-01',
        ]);
    }

    public function test_invalid_trace_id(): void
    {
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => '00-abcdefghijklmnopabcdefghijklmnop-' . self::SPAN_ID_BASE16 . '-01',
        ]);
    }

    public function test_invalid_trace_id_size(): void
    {
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => '00-' . self::TRACE_ID_BASE16 . '00-' . self::SPAN_ID_BASE16 . '-01',
        ]);
    }

    public function test_empty_span_id(): void
    {
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => '00-' . self::TRACE_ID_BASE16 . '--01',
        ]);
    }

    public function test_invalid_span_id(): void
    {
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => '00-' . self::TRACE_ID_BASE16 . '-abcdefghijklmnop-01',
        ]);
    }

    public function test_invalid_span_id_size(): void
    {
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => '00-' . self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '00-01',
        ]);
    }

    private function assertInvalid(array $carrier): void
    {
        $this->assertSame(
            Context::getCurrent(),
            $this->traceContextPropagator->extract($carrier),
        );
    }

    private function getSpanContext(Context $context): SpanContextInterface
    {
        return Span::fromContext($context)->getContext();
    }

    private function withSpanContext(SpanContextInterface $spanContext, Context $context): Context
    {
        return $context->withContextValue(Span::wrap($spanContext));
    }
}
