<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Extension\Propagator\B3;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextFactory;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Extension\Propagator\B3\B3DebugFlagContextKey;
use OpenTelemetry\Extension\Propagator\B3\B3SinglePropagator;
use OpenTelemetry\SDK\Trace\Span;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\Extension\Propagator\B3\B3SinglePropagator
 */
class B3SinglePropagatorTest extends TestCase
{
    private const TRACE_ID_BASE16 = 'ff000000000000000000000000000041';
    private const SPAN_ID_BASE16 = 'ff00000000000041';
    private const DEBUG_FLAG = 'd';
    private const B3_HEADER_SAMPLED = self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-1';
    private const B3_HEADER_NOT_SAMPLED = self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-0';
    private const B3_HEADER_DEBUG = self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-' . self::DEBUG_FLAG;
    private const B3_DENY_SAMPLING = '0';

    private $B3;

    private B3SinglePropagator $b3SinglePropagator;

    protected function setUp(): void
    {
        $this->b3SinglePropagator = B3SinglePropagator::getInstance();
        [$this->B3] = $this->b3SinglePropagator->fields();
    }

    public function test_fields(): void
    {
        $this->assertSame(
            ['b3'],
            $this->b3SinglePropagator->fields()
        );
    }

    public function test_inject_empty(): void
    {
        $carrier = [];
        $this->b3SinglePropagator->inject($carrier);
        $this->assertEmpty($carrier);
    }

    public function test_inject_invalid_context(): void
    {
        $carrier = [];
        $this
            ->b3SinglePropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContextFactory::create(
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
            ->b3SinglePropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContextFactory::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [$this->B3 => self::B3_HEADER_SAMPLED],
            $carrier
        );
    }

    public function test_inject_non_sampled_context(): void
    {
        $carrier = [];
        $this
            ->b3SinglePropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContextFactory::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [$this->B3 => self::B3_HEADER_NOT_SAMPLED],
            $carrier
        );
    }

    public function test_inject_debug_context(): void
    {
        $carrier = [];
        $this
            ->b3SinglePropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContextFactory::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
                    Context::getCurrent()
                )->with(B3DebugFlagContextKey::instance(), self::DEBUG_FLAG)
            );

        $this->assertSame(
            [$this->B3 => self::B3_HEADER_DEBUG],
            $carrier
        );
    }

    public function test_extract_nothing(): void
    {
        $this->assertSame(
            Context::getCurrent(),
            $this->b3SinglePropagator->extract([])
        );
    }

    /**
     * @dataProvider debugValueProvider
     */
    public function test_extract_debug_context($headerValue): void
    {
        $carrier = [
            $this->B3 => $headerValue,
        ];

        $context = $this->b3SinglePropagator->extract($carrier);

        $this->assertEquals(
            self::DEBUG_FLAG,
            $context->get(B3DebugFlagContextKey::instance())
        );

        $this->assertEquals(
            SpanContextFactory::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function debugValueProvider(): array
    {
        return [
            'String(lower string) debug value' => [self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-' . self::DEBUG_FLAG],
            'String(upper string) debug value' => [self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-' . strtoupper(self::DEBUG_FLAG)],
        ];
    }

    public function test_extract_sampled_context(): void
    {
        $carrier = [
            $this->B3 => self::B3_HEADER_SAMPLED,
        ];

        $context = $this->b3SinglePropagator->extract($carrier);

        $this->assertNull($context->get(B3DebugFlagContextKey::instance()));

        $this->assertEquals(
            SpanContextFactory::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_non_sampled_context(): void
    {
        $carrier = [
            $this->B3 => self::B3_HEADER_NOT_SAMPLED,
        ];

        $context = $this->b3SinglePropagator->extract($carrier);

        $this->assertNull($context->get(B3DebugFlagContextKey::instance()));

        $this->assertEquals(
            SpanContextFactory::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_sampled_context_with_parent_span_id(): void
    {
        $carrier = [
            $this->B3 => self::B3_HEADER_SAMPLED . '-' . self::TRACE_ID_BASE16,
        ];

        $context = $this->b3SinglePropagator->extract($carrier);

        $this->assertNull($context->get(B3DebugFlagContextKey::instance()));

        $this->assertEquals(
            SpanContextFactory::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_non_sampled_context_with_parent_span_id(): void
    {
        $carrier = [
            $this->B3 => self::B3_HEADER_NOT_SAMPLED . '-' . self::TRACE_ID_BASE16,
        ];

        $context = $this->b3SinglePropagator->extract($carrier);

        $this->assertNull($context->get(B3DebugFlagContextKey::instance()));

        $this->assertEquals(
            SpanContextFactory::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_defer_sampling(): void
    {
        $carrier = [
            $this->B3 => self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16,
        ];
        $this->assertEquals(
            SpanContextFactory::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
            $this->getSpanContext($this->b3SinglePropagator->extract($carrier))
        );
    }

    /**
     * @dataProvider invalidSampledValueProvider
     */
    public function test_extract_invalid_sampled_context($headerValue): void
    {
        $carrier = [
            $this->B3 => $headerValue,
        ];

        $this->assertEquals(
            SpanContextFactory::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
            $this->getSpanContext($this->b3SinglePropagator->extract($carrier))
        );
    }

    public function invalidSampledValueProvider(): array
    {
        return [
            'wrong sampled value' => [self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-wrong'],
            'empty sampled value' => [self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '-'],
        ];
    }

    public function test_extract_and_inject(): void
    {
        $extractCarrier = [
            $this->B3 => self::B3_HEADER_SAMPLED,
        ];
        $context = $this->b3SinglePropagator->extract($extractCarrier);
        $injectCarrier = [];
        $this->b3SinglePropagator->inject($injectCarrier, null, $context);
        $this->assertSame($injectCarrier, $extractCarrier);
    }

    public function test_extract_empty_header(): void
    {
        $this->assertInvalid([
            $this->B3 => '',
        ]);
    }

    public function test_extract_header_with_extra_flags(): void
    {
        $this->assertInvalid([
            $this->B3 => self::B3_HEADER_SAMPLED . '-extra-flags',
        ]);
    }

    public function test_extract_deny_sampling(): void
    {
        $this->assertInvalid([
            $this->B3 => self::B3_DENY_SAMPLING,
        ]);
    }

    public function test_empty_trace_id(): void
    {
        $this->assertInvalid([
            $this->B3 => '-' . self::SPAN_ID_BASE16 . '-1',
        ]);
    }
    public function test_invalid_trace_id(): void
    {
        $this->assertInvalid([
            $this->B3 => 'abcdefghijklmnopabcdefghijklmnop-' . self::SPAN_ID_BASE16 . '-1',
        ]);
    }

    public function test_invalid_trace_id_size(): void
    {
        $this->assertInvalid([
            $this->B3 => self::TRACE_ID_BASE16 . '00-' . self::SPAN_ID_BASE16 . '-1',
        ]);
    }

    public function test_empty_span_id(): void
    {
        $this->assertInvalid([
            $this->B3 => self::TRACE_ID_BASE16 . '--1',
        ]);
    }

    public function test_invalid_span_id(): void
    {
        $this->assertInvalid([
            $this->B3 => self::TRACE_ID_BASE16 . '-abcdefghijklmnop-1',
        ]);
    }

    public function test_invalid_span_id_size(): void
    {
        $this->assertInvalid([
            $this->B3 => self::TRACE_ID_BASE16 . '-' . self::SPAN_ID_BASE16 . '00-1',
        ]);
    }

    private function assertInvalid(array $carrier): void
    {
        $this->assertSame(
            Context::getCurrent(),
            $this->b3SinglePropagator->extract($carrier),
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
