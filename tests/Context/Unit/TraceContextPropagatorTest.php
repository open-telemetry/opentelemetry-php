<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Context\Unit;

use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Trace\SpanContext;
use OpenTelemetry\SDK\Trace\TraceState;
use PHPUnit\Framework\TestCase;

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

    public function test_inject_invalidContext(): void
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

    public function test_inject_sampledContext(): void
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

    public function test_inject_sampledContext_withTraceState(): void
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

    public function test_inject_nonSampledContext(): void
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

    public function test_inject_nonSampledContext_withTraceState(): void
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

    public function test_extract_sampledContext(): void
    {
        $carrier = [
            TraceContextPropagator::TRACEPARENT => self::TRACEPARENT_HEADER_SAMPLED,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($this->traceContextPropagator->extract($carrier))
        );
    }

    public function test_extract_sampledContext_withTraceState(): void
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

    public function test_extract_nonSampledContext(): void
    {
        $carrier = [
            TraceContextPropagator::TRACEPARENT => self::TRACEPARENT_HEADER_NOT_SAMPLED,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
            $this->getSpanContext($this->traceContextPropagator->extract($carrier))
        );
    }

    public function test_extract_nonSampledContext_withTraceState(): void
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

    public function test_extractAndInject(): void
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

    public function test_extract_traceStateWithSpaces(): void
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

    public function test_extract_emptyTraceState(): void
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

    public function test_extract_emptyHeader(): void
    {
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => '',
        ]);
    }

    public function test_invalidTraceId(): void
    {
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => '00-abcdefghijklmnopabcdefghijklmnop-' . self::SPAN_ID_BASE16 . '-01',
        ]);
    }

    public function test_invalidTraceId_size(): void
    {
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => '00-' . self::TRACE_ID_BASE16 . '00-' . self::SPAN_ID_BASE16 . '-01',
        ]);
    }

    public function test_invalidSpanId(): void
    {
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => '00-' . self::TRACE_ID_BASE16 . 'abcdefghijklmnop-01',
        ]);
    }

    public function test_invalidSpanId_size(): void
    {
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => '00-' . self::TRACE_ID_BASE16 . 'abcdefghijklmnop-00-01',
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
