<?php

declare(strict_types=1);

namesfinal pace OpenTelemetry\Tests\Unit\API\Trace\Propagation;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\API\Trace\TraceState;
use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Trace\Span;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TraceContextPropagator::class)]
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

    #[\Override]
    protected function setUp(): void
    {
        Logging::disable();
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
                        SpanContextValidator::INVALID_TRACE,
                        SpanContextValidator::INVALID_SPAN,
                        TraceFlags::SAMPLED
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
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::SAMPLED),
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
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::SAMPLED, $this->traceState),
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
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::DEFAULT, $this->traceState),
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
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::SAMPLED),
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
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::SAMPLED, $this->traceState),
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
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::DEFAULT, $this->traceState),
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
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::DEFAULT, $this->traceState),
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
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::DEFAULT, new TraceState()),
            $this->getSpanContext($this->traceContextPropagator->extract($carrier))
        );
    }

    public function test_extract_empty_header(): void
    {
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => '',
        ]);
    }

    public function test_extract_future_version(): void
    {
        $carrierFuture = [
            TraceContextPropagator::TRACEPARENT => 'aa-' . self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-' . '00',
            TraceContextPropagator::TRACESTATE => self::TRACESTATE_NOT_DEFAULT_ENCODING,
        ];

        // Tolerant of future versions with same parts.
        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::DEFAULT, $this->traceState),
            $this->getSpanContext($this->traceContextPropagator->extract($carrierFuture)),
        );

        $carrierFutureMoreParts = [
            TraceContextPropagator::TRACEPARENT => 'af-' . self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-' . '00' . '-000-this-is-the-future',
            TraceContextPropagator::TRACESTATE => self::TRACESTATE_NOT_DEFAULT_ENCODING,
        ];

        // Tolerant of future versions with more parts.
        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::DEFAULT, $this->traceState),
            $this->getSpanContext($this->traceContextPropagator->extract($carrierFutureMoreParts)),
        );
    }

    public function test_invalid_traceparent_version_0xff(): void
    {
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => 'ff-' . self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-' . '00',
        ]);
    }

    public function test_invalid_traceparent_version(): void
    {
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => 'aaa-' . self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-' . '00',
        ]);

        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => 'gx-' . self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-' . '00',
        ]);
    }

    public function test_invalid_trace_format(): void
    {
        // More than 4 parts to the trace but not a future version.
        $this->assertInvalid([
            TraceContextPropagator::TRACEPARENT => '00-' . self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-' . '00' . '-000-this-is-not-the-future',
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

    private function getSpanContext(ContextInterface $context): SpanContextInterface
    {
        return Span::fromContext($context)->getContext();
    }

    private function withSpanContext(SpanContextInterface $spanContext, ContextInterface $context): ContextInterface
    {
        return $context->withContextValue(Span::wrap($spanContext));
    }
}
