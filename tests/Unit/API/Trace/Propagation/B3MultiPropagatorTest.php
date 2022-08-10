<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace\Propagation;

use OpenTelemetry\API\Trace\Propagation\B3DebugFlagContextKey;
use OpenTelemetry\API\Trace\Propagation\B3MultiPropagator;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Span;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\API\Trace\Propagation\B3MultiPropagator
 */
class B3MultiPropagatorTest extends TestCase
{
    private const TRACE_ID_BASE16 = 'ff000000000000000000000000000041';
    private const SPAN_ID_BASE16 = 'ff00000000000041';
    private const IS_SAMPLED = '1';
    private const IS_NOT_SAMPLED = '0';

    private B3MultiPropagator $b3MultiPropagator;

    protected function setUp(): void
    {
        $this->b3MultiPropagator = B3MultiPropagator::getInstance();
    }

    public function test_fields(): void
    {
        $this->assertSame(
            ['X-B3-TraceId', 'X-B3-SpanId', 'X-B3-ParentSpanId', 'X-B3-Sampled', 'X-B3-Flags'],
            $this->b3MultiPropagator->fields()
        );
    }

    public function test_inject_empty(): void
    {
        $carrier = [];
        $this->b3MultiPropagator->inject($carrier);
        $this->assertEmpty($carrier);
    }

    public function test_inject_invalid_context(): void
    {
        $carrier = [];
        $this
            ->b3MultiPropagator
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
            ->b3MultiPropagator
            ->inject(
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

    public function test_inject_non_sampled_context(): void
    {
        $carrier = [];
        $this
            ->b3MultiPropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [
                B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
                B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
                B3MultiPropagator::SAMPLED => self::IS_NOT_SAMPLED,
            ],
            $carrier
        );
    }

    public function test_inject_debug_with_sampled_context(): void
    {
        $carrier = [];
        $this
            ->b3MultiPropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
                    Context::getCurrent()
                )->with(B3DebugFlagContextKey::instance(), self::IS_SAMPLED)
            );

        $this->assertSame(
            [
                B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
                B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
                B3MultiPropagator::DEBUG_FLAG => self::IS_SAMPLED,
            ],
            $carrier
        );
    }

    public function test_inject_debug_with_non_sampled_context(): void
    {
        $carrier = [];
        $this
            ->b3MultiPropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_DEFAULT),
                    Context::getCurrent()
                )->with(B3DebugFlagContextKey::instance(), self::IS_SAMPLED)
            );

        $this->assertSame(
            [
                B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
                B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
                B3MultiPropagator::DEBUG_FLAG => self::IS_SAMPLED,
            ],
            $carrier
        );
    }

    public function test_extract_nothing(): void
    {
        $this->assertSame(
            Context::getCurrent(),
            $this->b3MultiPropagator->extract([])
        );
    }

    public function test_extract_debug_context(): void
    {
        $carrier = [
            B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
            B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
            B3MultiPropagator::DEBUG_FLAG => self::IS_SAMPLED,
        ];

        $context = $this->b3MultiPropagator->extract($carrier);

        $this->assertEquals(
            self::IS_SAMPLED,
            $context->get(B3DebugFlagContextKey::instance())
        );

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_debug_with_sampled_context(): void
    {
        $carrier = [
            B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
            B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
            B3MultiPropagator::SAMPLED => self::IS_SAMPLED,
            B3MultiPropagator::DEBUG_FLAG => self::IS_SAMPLED,
        ];

        $context = $this->b3MultiPropagator->extract($carrier);

        $this->assertEquals(
            self::IS_SAMPLED,
            $context->get(B3DebugFlagContextKey::instance())
        );

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_debug_with_non_sampled_context(): void
    {
        $carrier = [
            B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
            B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
            B3MultiPropagator::SAMPLED => self::IS_NOT_SAMPLED,
            B3MultiPropagator::DEBUG_FLAG => self::IS_SAMPLED,
        ];

        $context = $this->b3MultiPropagator->extract($carrier);

        $this->assertEquals(
            self::IS_SAMPLED,
            $context->get(B3DebugFlagContextKey::instance())
        );

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($context)
        );
    }

    /**
     * @dataProvider sampledValueProvider
     */
    public function test_extract_sampled_context($sampledValue): void
    {
        $carrier = [
            B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
            B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
            B3MultiPropagator::SAMPLED => $sampledValue,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($this->b3MultiPropagator->extract($carrier))
        );
    }

    public function sampledValueProvider()
    {
        return [
            'String sampled value' => ['1'],
            'Boolean(lower string) sampled value' => ['true'],
            'Boolean(upper string) sampled value' => ['TRUE'],
            'Boolean(camel string) sampled value' => ['True'],
        ];
    }

    /**
     * @dataProvider notSampledValueProvider
     */
    public function test_extract_non_sampled_context($sampledValue): void
    {
        $carrier = [
            B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
            B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
            B3MultiPropagator::SAMPLED => $sampledValue,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
            $this->getSpanContext($this->b3MultiPropagator->extract($carrier))
        );
    }

    public function notSampledValueProvider()
    {
        return [
            'String sampled value' => ['0'],
            'Boolean(lower string) sampled value' => ['false'],
            'Boolean(upper string) sampled value' => ['FALSE'],
            'Boolean(camel string) sampled value' => ['False'],
        ];
    }

    /**
     * @dataProvider invalidDebugValueProvider
     */
    public function test_extract_invalid_debug_with_sampled_context($debugValue): void
    {
        $carrier = [
            B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
            B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
            B3MultiPropagator::SAMPLED => self::IS_SAMPLED,
            B3MultiPropagator::DEBUG_FLAG => $debugValue,
        ];

        $context = $this->b3MultiPropagator->extract($carrier);

        $this->assertNull($context->get(B3DebugFlagContextKey::instance()));

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_SAMPLED),
            $this->getSpanContext($context)
        );
    }

    /**
     * @dataProvider invalidDebugValueProvider
     */
    public function test_extract_invalid_debug_with_non_sampled_context($debugValue): void
    {
        $carrier = [
            B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
            B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
            B3MultiPropagator::SAMPLED => self::IS_NOT_SAMPLED,
            B3MultiPropagator::DEBUG_FLAG => $debugValue,
        ];

        $context = $this->b3MultiPropagator->extract($carrier);

        $this->assertNull($context->get(B3DebugFlagContextKey::instance()));

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, SpanContextInterface::TRACE_FLAG_DEFAULT),
            $this->getSpanContext($context)
        );
    }

    public function invalidDebugValueProvider()
    {
        return [
            'Invalid debug value - wrong type' => [1],
            'Invalid debug value - wrong character' => ['x'],
            'Invalid debug value - false' => ['false'],
            'Invalid debug value - true' => ['true'],
        ];
    }

    /**
     * @dataProvider invalidSampledValueProvider
     */
    public function test_extract_invalid_sampled_context($sampledValue): void
    {
        $carrier = [
            B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
            B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
            B3MultiPropagator::SAMPLED => $sampledValue,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
            $this->getSpanContext($this->b3MultiPropagator->extract($carrier))
        );
    }

    public function invalidSampledValueProvider()
    {
        return [
            'wrong sampled value' => ['wrong'],
            'null sampled value' => [null],
            'empty sampled value' => [[]],
        ];
    }

    public function test_extract_and_inject(): void
    {
        $extractCarrier = [
            B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
            B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
            B3MultiPropagator::SAMPLED => self::IS_SAMPLED,
        ];
        $context = $this->b3MultiPropagator->extract($extractCarrier);
        $injectCarrier = [];
        $this->b3MultiPropagator->inject($injectCarrier, null, $context);
        $this->assertSame($injectCarrier, $extractCarrier);
    }

    public function test_extract_empty_trace_id(): void
    {
        $this->assertInvalid(
            [
                B3MultiPropagator::TRACE_ID => '',
                B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
                B3MultiPropagator::SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    public function test_invalid_trace_id(): void
    {
        $this->assertInvalid(
            [
                B3MultiPropagator::TRACE_ID => 'abcdefghijklmnopabcdefghijklmnop',
                B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
                B3MultiPropagator::SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    public function test_invalid_trace_id_size(): void
    {
        $this->assertInvalid(
            [
                B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16 . '00',
                B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16,
                B3MultiPropagator::SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    public function test_extract_empty_span_id(): void
    {
        $this->assertInvalid(
            [
                B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
                B3MultiPropagator::SPAN_ID => '',
                B3MultiPropagator::SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    public function test_invalid_span_id(): void
    {
        $this->assertInvalid(
            [
                B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
                B3MultiPropagator::SPAN_ID => 'abcdefghijklmnop',
                B3MultiPropagator::SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    public function test_invalid_span_id_size(): void
    {
        $this->assertInvalid(
            [
                B3MultiPropagator::TRACE_ID => self::TRACE_ID_BASE16,
                B3MultiPropagator::SPAN_ID => self::SPAN_ID_BASE16 . '00',
                B3MultiPropagator::SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    private function assertInvalid(array $carrier): void
    {
        $this->assertSame(
            Context::getCurrent(),
            $this->b3MultiPropagator->extract($carrier),
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
