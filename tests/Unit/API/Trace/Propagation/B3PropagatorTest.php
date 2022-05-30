<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace\Propagation;

use OpenTelemetry\API\Trace\Propagation\B3Propagator;
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
    private const DEBUG_FLAG = 'd';
    private const B3_HEADER_SAMPLED = self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-1';
    private const B3_HEADER_NOT_SAMPLED = self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-0';
    private const B3_DENY_SAMPLING = '0';

    private B3Propagator $b3Propagator;

    protected function setUp(): void
    {
        $this->b3Propagator = B3Propagator::getInstance();
    }

    public function test_fields(): void
    {
        $this->assertSame(
            ['b3'],
            $this->b3Propagator->fields()
        );
    }

    public function test_inject_empty(): void
    {
        $carrier = [];
        $this->b3Propagator->inject($carrier);
        $this->assertEmpty($carrier);
    }

    public function test_inject_invalid_context(): void
    {
        $carrier = [];
        $this
            ->b3Propagator
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
            ->b3Propagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [B3Propagator::B3 => self::B3_HEADER_SAMPLED],
            $carrier
        );
    }

    public function test_inject_non_sampled_context(): void
    {
        $carrier = [];
        $this
            ->b3Propagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [B3Propagator::B3 => self::B3_DENY_SAMPLING],
            $carrier
        );
    }

    public function test_extract_nothing(): void
    {
        $this->assertSame(
            Context::getCurrent(),
            $this->b3Propagator->extract([])
        );
    }

    /**
     * @dataProvider debugValueProvider
     */
    public function test_extract_debug_context($headerValue): void
    {
        $carrier = [
            B3Propagator::B3 => $headerValue,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($this->b3Propagator->extract($carrier))
        );
    }

    public function debugValueProvider()
    {
        return [
            'String(lower string) debug value' => [self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-' . self::DEBUG_FLAG],
            'String(upper string) debug value' => [self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-' . strtoupper(self::DEBUG_FLAG)],
        ];
    }

    public function test_extract_sampled_context(): void
    {
        $carrier = [
            B3Propagator::B3 => self::B3_HEADER_SAMPLED,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($this->b3Propagator->extract($carrier))
        );
    }

    public function test_extract_non_sampled_context(): void
    {
        $carrier = [
            B3Propagator::B3 => self::B3_HEADER_NOT_SAMPLED,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
            $this->getSpanContext($this->b3Propagator->extract($carrier))
        );
    }

    public function test_extract_defer_sampling(): void
    {
        $carrier = [
            B3Propagator::B3 => self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16,
        ];
        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
            $this->getSpanContext($this->b3Propagator->extract($carrier))
        );
    }

    /**
     * @dataProvider invalidSampledValueProvider
     */
    public function test_extract_invalid_sampled_context($headerValue): void
    {
        $carrier = [
            B3Propagator::B3 => $headerValue,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
            $this->getSpanContext($this->b3Propagator->extract($carrier))
        );
    }

    public function invalidSampledValueProvider()
    {
        return [
            'wrong sampled value' => [self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-wrong'],
            'empty sampled value' => [self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-'],
        ];
    }

    public function test_extract_and_inject(): void
    {
        $extractCarrier = [
            B3Propagator::B3 => self::B3_HEADER_SAMPLED,
        ];
        $context = $this->b3Propagator->extract($extractCarrier);
        $injectCarrier = [];
        $this->b3Propagator->inject($injectCarrier, null, $context);
        $this->assertSame($injectCarrier, $extractCarrier);
    }

    public function test_extract_empty_header(): void
    {
        $this->assertInvalid([
            B3Propagator::B3 => '',
        ]);
    }

    public function test_extract_header_with_extra_flags(): void
    {
        $this->assertInvalid([
            B3Propagator::B3 => self::B3_HEADER_SAMPLED . '-extra-flags',
        ]);
    }

    public function test_extract_deny_sampling(): void
    {
        $this->assertInvalid([
            B3Propagator::B3 => self::B3_DENY_SAMPLING,
        ]);
    }

    public function test_empty_trace_id(): void
    {
        $this->assertInvalid([
            B3Propagator::B3 => '-' . self::SPAN_ID_BASE16 . '-1',
        ]);
    }
    public function test_invalid_trace_id(): void
    {
        $this->assertInvalid([
            B3Propagator::B3 => 'abcdefghijklmnopabcdefghijklmnop-' . self::SPAN_ID_BASE16 . '-1',
        ]);
    }

    public function test_invalid_trace_id_size(): void
    {
        $this->assertInvalid([
            B3Propagator::B3 => self::TRACE_ID_BASE16 . '00-' . self::SPAN_ID_BASE16 . '-1',
        ]);
    }

    public function test_empty_span_id(): void
    {
        $this->assertInvalid([
            B3Propagator::B3 => self::TRACE_ID_BASE16 . '--1',
        ]);
    }

    public function test_invalid_span_id(): void
    {
        $this->assertInvalid([
            B3Propagator::B3 => self::TRACE_ID_BASE16 . '-abcdefghijklmnop-1',
        ]);
    }

    public function test_invalid_span_id_size(): void
    {
        $this->assertInvalid([
            B3Propagator::B3 => self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '00-1',
        ]);
    }

    private function assertInvalid(array $carrier): void
    {
        $this->assertSame(
            Context::getCurrent(),
            $this->b3Propagator->extract($carrier),
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
