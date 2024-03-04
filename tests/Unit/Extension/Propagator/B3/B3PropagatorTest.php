<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Extension\Propagator\B3;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Extension\Propagator\B3\B3DebugFlagContextKey;
use OpenTelemetry\Extension\Propagator\B3\B3MultiPropagator;
use OpenTelemetry\Extension\Propagator\B3\B3Propagator;
use OpenTelemetry\Extension\Propagator\B3\B3SinglePropagator;
use OpenTelemetry\SDK\Trace\Span;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Extension\Propagator\B3\B3Propagator
 */
class B3PropagatorTest extends TestCase
{
    private const B3_TRACE_ID_16_CHAR = 'ff00051791e00041';
    private const B3_TRACE_ID = 'ff0000000000051791e0000000000041';
    private const B3_SPAN_ID = 'ff00051791e00041';
    private const B3_SINGLE_HEADER_SAMPLED = self::B3_TRACE_ID . '-' . self::B3_SPAN_ID . '-1';
    private const IS_SAMPLED = '1';
    private const IS_NOT_SAMPLED = '0';

    private string $b3;
    private string $traceId;
    private string $spanId;
    private string $sampled;

    /**
     * @psalm-suppress PossiblyUndefinedArrayOffset
     */
    public function setUp(): void
    {
        [$this->b3] = B3SinglePropagator::getInstance()->fields();
        $b3MultiFields = B3MultiPropagator::getInstance()->fields();
        $this->traceId = $b3MultiFields[0];
        $this->spanId = $b3MultiFields[1];
        $this->sampled = $b3MultiFields[3];
    }

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
                SpanContext::create(self::B3_TRACE_ID, self::B3_SPAN_ID, TraceFlags::SAMPLED),
                Context::getCurrent()
            )
        );

        $this->assertSame(
            [
                $this->traceId => self::B3_TRACE_ID,
                $this->spanId => self::B3_SPAN_ID,
                $this->sampled => self::IS_SAMPLED,
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
                SpanContext::create(self::B3_TRACE_ID, self::B3_SPAN_ID, TraceFlags::SAMPLED),
                Context::getCurrent()
            )
        );

        $this->assertSame(
            [$this->b3 => self::B3_SINGLE_HEADER_SAMPLED],
            $carrier
        );
    }

    public function test_extract_only_b3single_sampled_context_with_b3single_instance(): void
    {
        $carrier = [
            $this->b3 => self::B3_SINGLE_HEADER_SAMPLED,
        ];

        $propagator = B3Propagator::getB3SingleHeaderInstance();

        $context = $propagator->extract($carrier);

        $this->assertNull($context->get(B3DebugFlagContextKey::instance()));

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::B3_TRACE_ID, self::B3_SPAN_ID, TraceFlags::SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_only_b3single_sampled_context_with_b3multi_instance(): void
    {
        $carrier = [
            $this->b3 => self::B3_SINGLE_HEADER_SAMPLED,
        ];

        $propagator = B3Propagator::getB3MultiHeaderInstance();

        $context = $propagator->extract($carrier);

        $this->assertNull($context->get(B3DebugFlagContextKey::instance()));

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::B3_TRACE_ID, self::B3_SPAN_ID, TraceFlags::SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_only_b3multi_sampled_context_with_b3single_instance(): void
    {
        $carrier = [
            $this->traceId => self::B3_TRACE_ID,
            $this->spanId => self::B3_SPAN_ID,
            $this->sampled => self::IS_SAMPLED,
        ];

        $propagator = B3Propagator::getB3SingleHeaderInstance();

        $context = $propagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::B3_TRACE_ID, self::B3_SPAN_ID, TraceFlags::SAMPLED),
            $this->getSpanContext($context)
        );
    }

    /**
     * @dataProvider validTraceIdProvider
     */
    public function test_extract_only_b3multi_sampled_context_with_b3multi_instance(string $traceId, string $expected): void
    {
        $carrier = [
            $this->traceId => $traceId,
            $this->spanId => self::B3_SPAN_ID,
            $this->sampled => self::IS_SAMPLED,
        ];

        $propagator = B3Propagator::getB3MultiHeaderInstance();

        $context = $propagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent($expected, self::B3_SPAN_ID, TraceFlags::SAMPLED),
            $this->getSpanContext($context)
        );
    }

    /**
     * @dataProvider validTraceIdProvider
     */
    public function test_extract_b3_single(string $traceId, string $expected): void
    {
        $carrier = [
            'b3' => $traceId . '-' . self::B3_SPAN_ID,
        ];
        $context = B3Propagator::getB3SingleHeaderInstance()->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent($expected, self::B3_SPAN_ID, TraceFlags::DEFAULT),
            $this->getSpanContext($context)
        );
    }

    public static function validTraceIdProvider(): array
    {
        return [
            '16 char trace id' => [
                self::B3_TRACE_ID_16_CHAR,
                str_pad(self::B3_TRACE_ID_16_CHAR, 32, '0', STR_PAD_LEFT),
            ],
            '32 char trace id' => [
                self::B3_TRACE_ID,
                self::B3_TRACE_ID,
            ],
        ];
    }

    public function test_extract_both_sampled_context_with_b3single_instance(): void
    {
        $carrier = [
            $this->traceId => self::B3_TRACE_ID,
            $this->spanId => self::B3_SPAN_ID,
            $this->sampled => self::IS_NOT_SAMPLED,
            $this->b3 => self::B3_SINGLE_HEADER_SAMPLED,
        ];

        $propagator = B3Propagator::getB3SingleHeaderInstance();

        $context = $propagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::B3_TRACE_ID, self::B3_SPAN_ID, TraceFlags::SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_both_sampled_context_with_b3multi_instance(): void
    {
        $carrier = [
            $this->traceId => self::B3_TRACE_ID,
            $this->spanId => self::B3_SPAN_ID,
            $this->sampled => self::IS_NOT_SAMPLED,
            $this->b3 => self::B3_SINGLE_HEADER_SAMPLED,
        ];

        $propagator = B3Propagator::getB3MultiHeaderInstance();

        $context = $propagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::B3_TRACE_ID, self::B3_SPAN_ID, TraceFlags::SAMPLED),
            $this->getSpanContext($context)
        );
    }

    /**
     * @dataProvider invalidB3SingleHeaderValueProvider
     */
    public function test_extract_b3_single_invalid_and_b3_multi_valid_context_with_b3single_instance($headerValue): void
    {
        $carrier = [
            $this->traceId => self::B3_TRACE_ID,
            $this->spanId => self::B3_SPAN_ID,
            $this->sampled => self::IS_NOT_SAMPLED,
            $this->b3 => $headerValue,
        ];

        $propagator = B3Propagator::getB3SingleHeaderInstance();

        $context = $propagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::B3_TRACE_ID, self::B3_SPAN_ID, TraceFlags::DEFAULT),
            $this->getSpanContext($context)
        );
    }

    /**
     * @dataProvider invalidB3SingleHeaderValueProvider
     */
    public function test_extract_b3_single_invalid_and_b3_multi_valid_context_with_b3multi_instance($headerValue): void
    {
        $carrier = [
            $this->traceId => self::B3_TRACE_ID,
            $this->spanId => self::B3_SPAN_ID,
            $this->sampled => self::IS_NOT_SAMPLED,
            $this->b3 => $headerValue,
        ];

        $propagator = B3Propagator::getB3MultiHeaderInstance();

        $context = $propagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::B3_TRACE_ID, self::B3_SPAN_ID, TraceFlags::DEFAULT),
            $this->getSpanContext($context)
        );
    }

    public static function invalidB3SingleHeaderValueProvider(): array
    {
        return [
            'invalid traceid' => ['abcdefghijklmnopabcdefghijklmnop-' . self::B3_SPAN_ID . '-1'],
            'invalid spanid' => [self::B3_TRACE_ID . '-abcdefghijklmnop-1'],
        ];
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
