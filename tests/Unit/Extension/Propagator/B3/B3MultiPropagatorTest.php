<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Extension\Propagator\B3;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Extension\Propagator\B3\B3DebugFlagContextKey;
use OpenTelemetry\Extension\Propagator\B3\B3MultiPropagator;
use OpenTelemetry\SDK\Trace\Span;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(B3MultiPropagator::class)]
class B3MultiPropagatorTest extends TestCase
{
    private const TRACE_ID_BASE16 = 'ff000000000000000000000000000041';
    private const SPAN_ID_BASE16 = 'ff00000000000041';
    private const IS_SAMPLED = '1';
    private const IS_NOT_SAMPLED = '0';

    private $TRACE_ID;
    private $SPAN_ID;
    private $SAMPLED;
    private $DEBUG_FLAG;

    private B3MultiPropagator $b3MultiPropagator;

    protected function setUp(): void
    {
        $this->b3MultiPropagator = B3MultiPropagator::getInstance();
        $b3MultiFields = $this->b3MultiPropagator->fields();
        $this->TRACE_ID = $b3MultiFields[0];
        $this->SPAN_ID = $b3MultiFields[1];
        $this->SAMPLED = $b3MultiFields[3];
        $this->DEBUG_FLAG = $b3MultiFields[4];
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
            ->b3MultiPropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::SAMPLED),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [
                $this->TRACE_ID => self::TRACE_ID_BASE16,
                $this->SPAN_ID => self::SPAN_ID_BASE16,
                $this->SAMPLED => self::IS_SAMPLED,
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
                $this->TRACE_ID => self::TRACE_ID_BASE16,
                $this->SPAN_ID => self::SPAN_ID_BASE16,
                $this->SAMPLED => self::IS_NOT_SAMPLED,
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
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::SAMPLED),
                    Context::getCurrent()
                )->with(B3DebugFlagContextKey::instance(), self::IS_SAMPLED)
            );

        $this->assertSame(
            [
                $this->TRACE_ID => self::TRACE_ID_BASE16,
                $this->SPAN_ID => self::SPAN_ID_BASE16,
                $this->DEBUG_FLAG => self::IS_SAMPLED,
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
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::DEFAULT),
                    Context::getCurrent()
                )->with(B3DebugFlagContextKey::instance(), self::IS_SAMPLED)
            );

        $this->assertSame(
            [
                $this->TRACE_ID => self::TRACE_ID_BASE16,
                $this->SPAN_ID => self::SPAN_ID_BASE16,
                $this->DEBUG_FLAG => self::IS_SAMPLED,
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
            $this->TRACE_ID => self::TRACE_ID_BASE16,
            $this->SPAN_ID => self::SPAN_ID_BASE16,
            $this->DEBUG_FLAG => self::IS_SAMPLED,
        ];

        $context = $this->b3MultiPropagator->extract($carrier);

        $this->assertEquals(
            self::IS_SAMPLED,
            $context->get(B3DebugFlagContextKey::instance())
        );

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_debug_with_sampled_context(): void
    {
        $carrier = [
            $this->TRACE_ID => self::TRACE_ID_BASE16,
            $this->SPAN_ID => self::SPAN_ID_BASE16,
            $this->SAMPLED => self::IS_SAMPLED,
            $this->DEBUG_FLAG => self::IS_SAMPLED,
        ];

        $context = $this->b3MultiPropagator->extract($carrier);

        $this->assertEquals(
            self::IS_SAMPLED,
            $context->get(B3DebugFlagContextKey::instance())
        );

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_debug_with_non_sampled_context(): void
    {
        $carrier = [
            $this->TRACE_ID => self::TRACE_ID_BASE16,
            $this->SPAN_ID => self::SPAN_ID_BASE16,
            $this->SAMPLED => self::IS_NOT_SAMPLED,
            $this->DEBUG_FLAG => self::IS_SAMPLED,
        ];

        $context = $this->b3MultiPropagator->extract($carrier);

        $this->assertEquals(
            self::IS_SAMPLED,
            $context->get(B3DebugFlagContextKey::instance())
        );

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::SAMPLED),
            $this->getSpanContext($context)
        );
    }

    #[DataProvider('sampledValueProvider')]
    public function test_extract_sampled_context($sampledValue): void
    {
        $carrier = [
            $this->TRACE_ID => self::TRACE_ID_BASE16,
            $this->SPAN_ID => self::SPAN_ID_BASE16,
            $this->SAMPLED => $sampledValue,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::SAMPLED),
            $this->getSpanContext($this->b3MultiPropagator->extract($carrier))
        );
    }

    public static function sampledValueProvider(): array
    {
        return [
            'String sampled value' => ['1'],
            'Boolean(lower string) sampled value' => ['true'],
            'Boolean(upper string) sampled value' => ['TRUE'],
            'Boolean(camel string) sampled value' => ['True'],
        ];
    }

    #[DataProvider('notSampledValueProvider')]
    public function test_extract_non_sampled_context($sampledValue): void
    {
        $carrier = [
            $this->TRACE_ID => self::TRACE_ID_BASE16,
            $this->SPAN_ID => self::SPAN_ID_BASE16,
            $this->SAMPLED => $sampledValue,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
            $this->getSpanContext($this->b3MultiPropagator->extract($carrier))
        );
    }

    public static function notSampledValueProvider(): array
    {
        return [
            'String sampled value' => ['0'],
            'Boolean(lower string) sampled value' => ['false'],
            'Boolean(upper string) sampled value' => ['FALSE'],
            'Boolean(camel string) sampled value' => ['False'],
        ];
    }

    #[DataProvider('invalidDebugValueProvider')]
    public function test_extract_invalid_debug_with_sampled_context($debugValue): void
    {
        $carrier = [
            $this->TRACE_ID => self::TRACE_ID_BASE16,
            $this->SPAN_ID => self::SPAN_ID_BASE16,
            $this->SAMPLED => self::IS_SAMPLED,
            $this->DEBUG_FLAG => $debugValue,
        ];

        $context = $this->b3MultiPropagator->extract($carrier);

        $this->assertNull($context->get(B3DebugFlagContextKey::instance()));

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::SAMPLED),
            $this->getSpanContext($context)
        );
    }

    #[DataProvider('invalidDebugValueProvider')]
    public function test_extract_invalid_debug_with_non_sampled_context($debugValue): void
    {
        $carrier = [
            $this->TRACE_ID => self::TRACE_ID_BASE16,
            $this->SPAN_ID => self::SPAN_ID_BASE16,
            $this->SAMPLED => self::IS_NOT_SAMPLED,
            $this->DEBUG_FLAG => $debugValue,
        ];

        $context = $this->b3MultiPropagator->extract($carrier);

        $this->assertNull($context->get(B3DebugFlagContextKey::instance()));

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::DEFAULT),
            $this->getSpanContext($context)
        );
    }

    public static function invalidDebugValueProvider(): array
    {
        return [
            'Invalid debug value - wrong type' => [1],
            'Invalid debug value - wrong character' => ['x'],
            'Invalid debug value - false' => ['false'],
            'Invalid debug value - true' => ['true'],
        ];
    }

    #[DataProvider('invalidSampledValueProvider')]
    public function test_extract_invalid_sampled_context($sampledValue): void
    {
        $carrier = [
            $this->TRACE_ID => self::TRACE_ID_BASE16,
            $this->SPAN_ID => self::SPAN_ID_BASE16,
            $this->SAMPLED => $sampledValue,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
            $this->getSpanContext($this->b3MultiPropagator->extract($carrier))
        );
    }

    public static function invalidSampledValueProvider(): array
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
            $this->TRACE_ID => self::TRACE_ID_BASE16,
            $this->SPAN_ID => self::SPAN_ID_BASE16,
            $this->SAMPLED => self::IS_SAMPLED,
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
                $this->TRACE_ID => '',
                $this->SPAN_ID => self::SPAN_ID_BASE16,
                $this->SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    public function test_invalid_trace_id(): void
    {
        $this->assertInvalid(
            [
                $this->TRACE_ID => 'abcdefghijklmnopabcdefghijklmnop',
                $this->SPAN_ID => self::SPAN_ID_BASE16,
                $this->SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    public function test_invalid_trace_id_size(): void
    {
        $this->assertInvalid(
            [
                $this->TRACE_ID => self::TRACE_ID_BASE16 . '00',
                $this->SPAN_ID => self::SPAN_ID_BASE16,
                $this->SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    public function test_extract_empty_span_id(): void
    {
        $this->assertInvalid(
            [
                $this->TRACE_ID => self::TRACE_ID_BASE16,
                $this->SPAN_ID => '',
                $this->SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    public function test_invalid_span_id(): void
    {
        $this->assertInvalid(
            [
                $this->TRACE_ID => self::TRACE_ID_BASE16,
                $this->SPAN_ID => 'abcdefghijklmnop',
                $this->SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    public function test_invalid_span_id_size(): void
    {
        $this->assertInvalid(
            [
                $this->TRACE_ID => self::TRACE_ID_BASE16,
                $this->SPAN_ID => self::SPAN_ID_BASE16 . '00',
                $this->SAMPLED => self::IS_SAMPLED,
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

    private function getSpanContext(ContextInterface $context): SpanContextInterface
    {
        return Span::fromContext($context)->getContext();
    }

    private function withSpanContext(SpanContextInterface $spanContext, ContextInterface $context): ContextInterface
    {
        return $context->withContextValue(Span::wrap($spanContext));
    }
}
