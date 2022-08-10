<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace\Propagation;

use OpenTelemetry\API\Trace\Propagation\B3DebugFlagContextKey;
use OpenTelemetry\API\Trace\Propagation\B3MultiPropagator;
use OpenTelemetry\API\Trace\Propagation\B3Propagator;
use OpenTelemetry\API\Trace\Propagation\B3SinglePropagator;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Span;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\API\Trace\Propagation\B3Propagator
 */
class B3PropagatorTest extends TestCase
{
    private const TRACE_ID_BASE16 = 'ff000000000000000000000000000041';
    private const SPAN_ID_BASE16 = 'ff00000000000041';
    private const B3_HEADER_SAMPLED = self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-1';
    private const IS_SAMPLED = '1';

    public function test_b3multi_fields(): void
    {
        $propagator = B3Propagator::getB3MultiHeaderInstance();
        $this->assertSame(
            ['X-B3-TraceId', 'X-B3-SpanId', 'X-B3-ParentSpanId', 'X-B3-Sampled', 'X-B3-Flags'],
            $propagator->fields()
        );
    }

    public function test_b3single_fields(): void
    {
        $propagator = B3Propagator::getB3SingleHeaderInstance();
        $this->assertSame(
            ['b3'],
            $propagator->fields()
        );
    }

    public function test_b3multi_inject(): void
    {
        $propagator = B3Propagator::getB3MultiHeaderInstance();
        $carrier = [];
        $propagator->inject(
            $carrier,
            null,
            $this->withSpanContext(
                SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
                Context::getCurrent()
            )
        );

        $this->assertSame(
            [
                B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
                B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
                B3MultiPropagator::SAMPLED => self::IS_SAMPLED,
            ],
            $carrier
        );
    }

    public function test_b3single_inject(): void
    {
        $propagator = B3Propagator::getB3SingleHeaderInstance();
        $carrier = [];
        $propagator->inject(
            $carrier,
            null,
            $this->withSpanContext(
                SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
                Context::getCurrent()
            )
        );

        $this->assertSame(
            [B3SinglePropagator::B3 => self::B3_HEADER_SAMPLED],
            $carrier
        );
    }

    public function test_extract_only_b3single_sampled_context_with_b3single_instance(): void
    {
        $carrier = [
            B3SinglePropagator::B3 => self::B3_HEADER_SAMPLED,
        ];

        $propagator = B3Propagator::getB3SingleHeaderInstance();

        $context = $propagator->extract($carrier);

        $this->assertNull($context->get(B3DebugFlagContextKey::instance()));

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_only_b3single_sampled_context_with_b3multi_instance(): void
    {
        $carrier = [
            B3SinglePropagator::B3 => self::B3_HEADER_SAMPLED,
        ];

        $propagator = B3Propagator::getB3MultiHeaderInstance();

        $context = $propagator->extract($carrier);

        $this->assertNull($context->get(B3DebugFlagContextKey::instance()));

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_only_b3multi_sampled_context_with_b3single_instance(): void
    {
        $carrier = [
            B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
            B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
            B3MultiPropagator::SAMPLED => self::IS_SAMPLED,
        ];

        $propagator = B3Propagator::getB3SingleHeaderInstance();

        $context = $propagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_only_b3multi_sampled_context_with_b3multi_instance(): void
    {
        $carrier = [
            B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
            B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
            B3MultiPropagator::SAMPLED => self::IS_SAMPLED,
        ];

        $propagator = B3Propagator::getB3MultiHeaderInstance();

        $context = $propagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_both_sampled_context_with_b3single_instance(): void
    {
        $carrier = [
            B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
            B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
            B3MultiPropagator::SAMPLED => self::IS_SAMPLED,
            B3SinglePropagator::B3 => self::B3_HEADER_SAMPLED,
        ];

        $propagator = B3Propagator::getB3SingleHeaderInstance();

        $context = $propagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_both_sampled_context_with_b3multi_instance(): void
    {
        $carrier = [
            B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
            B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
            B3MultiPropagator::SAMPLED => self::IS_SAMPLED,
            B3SinglePropagator::B3 => self::B3_HEADER_SAMPLED,
        ];

        $propagator = B3Propagator::getB3MultiHeaderInstance();

        $context = $propagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($context)
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
