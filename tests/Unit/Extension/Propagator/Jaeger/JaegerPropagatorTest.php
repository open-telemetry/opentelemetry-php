<?php

declare(strict_types=1);
final 
namespace OpenTelemetry\Tests\Unit\Extension\Propagator\Jaeger;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Extension\Propagator\Jaeger\JaegerDebugFlagContextKey;
use OpenTelemetry\Extension\Propagator\Jaeger\JaegerPropagator;
use OpenTelemetry\SDK\Trace\Span;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JaegerPropagator::class)]
class JaegerPropagatorTest extends TestCase
{
    private const TRACE_ID_BASE16 = '6bec5956ce56d66eb47802ab1cf6c4a0';
    private const SPAN_ID_BASE16 = '18dc27d6fabb2c47';
    private const TRACE_ID_SHORT = '53ce929d0e0e4736';
    private const SPAN_ID_SHORT = 'deadbef0';
    private const DEBUG_FLAG = '2';

    private TextMapPropagatorInterface $propagator;
    private string $fields;

    private function withSpanContext(SpanContextInterface $spanContext, ContextInterface $context): ContextInterface
    {
        return $context->withContextValue(Span::wrap($spanContext));
    }

    private function generateTraceIdHeaderValue(
        string $traceId,
        string $spanId,
        string $flag,
    ): string {
        return sprintf(
            '%s:%s:0:%s',
            $traceId,
            $spanId,
            $flag
        );
    }

    /**
     * @psalm-suppress PossiblyUndefinedArrayOffset
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->propagator = JaegerPropagator::getInstance();
        [$this->fields] = $this->propagator->fields();
    }

    public function test_fields(): void
    {
        $this->assertSame(
            ['uber-trace-id'],
            $this->propagator->fields()
        );
    }

    public function test_inject_invalid_context(): void
    {
        $carrier = [];
        $this->propagator->inject(
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
        $this->propagator->inject(
            $carrier,
            null,
            $this->withSpanContext(
                SpanContext::create(
                    self::TRACE_ID_BASE16,
                    self::SPAN_ID_BASE16,
                    TraceFlags::SAMPLED
                ),
                Context::getCurrent()
            )
        );

        $this->assertSame(
            [$this->fields => $this->generateTraceIdHeaderValue(
                self::TRACE_ID_BASE16,
                self::SPAN_ID_BASE16,
                '1'
            )],
            $carrier
        );
    }

    public function test_inject_not_sampled_context(): void
    {
        $carrier = [];
        $this->propagator->inject(
            $carrier,
            null,
            $this->withSpanContext(
                SpanContext::create(
                    self::TRACE_ID_BASE16,
                    self::SPAN_ID_BASE16,
                ),
                Context::getCurrent()
            )
        );

        $this->assertSame(
            [$this->fields => $this->generateTraceIdHeaderValue(
                self::TRACE_ID_BASE16,
                self::SPAN_ID_BASE16,
                '0'
            )],
            $carrier
        );
    }

    public function test_inject_null_context(): void
    {
        $carrier = [];
        $this->propagator->inject(
            $carrier
        );

        $this->assertEmpty($carrier);
    }

    public function test_inject_sampled_with_debug_context(): void
    {
        $carrier = [];
        $this->propagator->inject(
            $carrier,
            null,
            $this->withSpanContext(
                SpanContext::create(
                    self::TRACE_ID_BASE16,
                    self::SPAN_ID_BASE16,
                    TraceFlags::SAMPLED
                ),
                Context::getCurrent()
            )->with(JaegerDebugFlagContextKey::instance(), self::DEBUG_FLAG)
        );

        $this->assertSame(
            [$this->fields => $this->generateTraceIdHeaderValue(
                self::TRACE_ID_BASE16,
                self::SPAN_ID_BASE16,
                '3'
            )],
            $carrier
        );
    }

    public function test_inject_not_sampled_with_debug_context(): void
    {
        $carrier = [];
        $this->propagator->inject(
            $carrier,
            null,
            $this->withSpanContext(
                SpanContext::create(
                    self::TRACE_ID_BASE16,
                    self::SPAN_ID_BASE16,
                ),
                Context::getCurrent()
            )->with(JaegerDebugFlagContextKey::instance(), self::DEBUG_FLAG)
        );

        $this->assertSame(
            [$this->fields => $this->generateTraceIdHeaderValue(
                self::TRACE_ID_BASE16,
                self::SPAN_ID_BASE16,
                '0'
            )],
            $carrier
        );
    }

    public function test_extract_nothing(): void
    {
        $this->assertSame(
            Context::getCurrent(),
            $this->propagator->extract([])
        );
    }

    public function test_extract_sampled_context(): void
    {
        $carrier = [
            $this->fields => $this->generateTraceIdHeaderValue(
                self::TRACE_ID_BASE16,
                self::SPAN_ID_BASE16,
                '1'
            ),
        ];

        $context = $this->propagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(
                self::TRACE_ID_BASE16,
                self::SPAN_ID_BASE16,
                TraceFlags::SAMPLED
            ),
            Span::fromContext($context)->getContext()
        );
    }

    public function test_extract_not_sampled_context(): void
    {
        $carrier = [
            $this->fields => $this->generateTraceIdHeaderValue(
                self::TRACE_ID_BASE16,
                self::SPAN_ID_BASE16,
                '0'
            ),
        ];

        $context = $this->propagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(
                self::TRACE_ID_BASE16,
                self::SPAN_ID_BASE16
            ),
            Span::fromContext($context)->getContext()
        );
    }

    public function test_extract_debug_context(): void
    {
        $carrier = [
            $this->fields => $this->generateTraceIdHeaderValue(
                self::TRACE_ID_BASE16,
                self::SPAN_ID_BASE16,
                '2'
            ),
        ];

        $context = $this->propagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(
                self::TRACE_ID_BASE16,
                self::SPAN_ID_BASE16
            ),
            Span::fromContext($context)->getContext()
        );
    }

    public function test_extract_invalid_uber_trace_id(): void
    {
        $carrier = [
            $this->fields => '000000000000000000000000deadbeef:00000000deadbef0:00',
        ];

        $context = $this->propagator->extract($carrier);

        $this->assertSame(
            Context::getCurrent(),
            $context
        );
    }

    public function test_extract_invalid_trace_id(): void
    {
        $carrier = [
            $this->fields => $this->generateTraceIdHeaderValue(
                SpanContextValidator::INVALID_TRACE,
                '00000000deadbef0',
                '1'
            ),
        ];

        $context = $this->propagator->extract($carrier);

        $this->assertSame(
            Context::getCurrent(),
            $context
        );
    }

    public function test_extract_invalid_span_id(): void
    {
        $carrier = [
            $this->fields => $this->generateTraceIdHeaderValue(
                '000000000000000053ce929d0e0e4736',
                SpanContextValidator::INVALID_SPAN,
                '1'
            ),
        ];

        $context = $this->propagator->extract($carrier);

        $this->assertSame(
            Context::getCurrent(),
            $context
        );
    }

    public function test_extract_short_trace_id(): void
    {
        $carrier = [
            $this->fields => $this->generateTraceIdHeaderValue(
                self::TRACE_ID_SHORT,
                '00000000deadbef0',
                '1'
            ),
        ];

        $context = $this->propagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(
                '000000000000000053ce929d0e0e4736',
                '00000000deadbef0',
                TraceFlags::SAMPLED
            ),
            Span::fromContext($context)->getContext()
        );
    }

    public function test_extract_short_span_id(): void
    {
        $carrier = [
            $this->fields => $this->generateTraceIdHeaderValue(
                '000000000000000053ce929d0e0e4736',
                self::SPAN_ID_SHORT,
                '1'
            ),
        ];

        $context = $this->propagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(
                '000000000000000053ce929d0e0e4736',
                '00000000deadbef0',
                TraceFlags::SAMPLED
            ),
            Span::fromContext($context)->getContext()
        );
    }
}
