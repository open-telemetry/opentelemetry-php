<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Extension\Propagator\Instana;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\Context\Context;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Extension\Propagator\Instana\InstanaMultiPropagator;
use OpenTelemetry\SDK\Trace\Span;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InstanaMultiPropagator::class)]
class InstanaMultiPropagatorTest extends TestCase
{
    private const X_INSTANA_T = 'ff000000000000000000000000000041';
    private const X_INSTANA_S = 'ff00000000000041';
    private const IS_SAMPLED = '1';
    private const IS_NOT_SAMPLED = '0';

    private $TRACE_ID;
    private $SPAN_ID;
    private $SAMPLED;

    private InstanaMultiPropagator $instanaMultiPropagator;

    protected function setUp(): void
    {
        $this->instanaMultiPropagator = InstanaMultiPropagator::getInstance();
        $instanaMultiFields = $this->instanaMultiPropagator->fields();
        $this->TRACE_ID = $instanaMultiFields[0];
        $this->SPAN_ID = $instanaMultiFields[1];
        $this->SAMPLED = $instanaMultiFields[2];
    }

    public function test_fields(): void
    {
        $this->assertSame(
            ['X-INSTANA-T', 'X-INSTANA-S',  'X-INSTANA-L'],
            $this->instanaMultiPropagator->fields()
        );
    }

    public function test_inject_empty(): void
    {
        $carrier = [];
        $this->instanaMultiPropagator->inject($carrier);
        $this->assertEmpty($carrier);
    }

    public function test_inject_invalid_context(): void
    {
        $carrier = [];
        $this
            ->instanaMultiPropagator
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
            ->instanaMultiPropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::X_INSTANA_T, self::X_INSTANA_S, TraceFlags::SAMPLED),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [
                $this->TRACE_ID => self::X_INSTANA_T,
                $this->SPAN_ID => self::X_INSTANA_S,
                $this->SAMPLED => self::IS_SAMPLED,
            ],
            $carrier
        );
    }

    public function test_inject_non_sampled_context(): void
    {
        $carrier = [];
        $this
            ->instanaMultiPropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::X_INSTANA_T, self::X_INSTANA_S),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [
                $this->TRACE_ID => self::X_INSTANA_T,
                $this->SPAN_ID => self::X_INSTANA_S,
                $this->SAMPLED => self::IS_NOT_SAMPLED,
            ],
            $carrier
        );
    }

    public function test_inject_sampled_context_when_other_traceflags_set(): void
    {
        $carrier = [];
        $this
            ->instanaMultiPropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::X_INSTANA_T, self::X_INSTANA_S, traceFlags: 81),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [
                $this->TRACE_ID => self::X_INSTANA_T,
                $this->SPAN_ID => self::X_INSTANA_S,
                $this->SAMPLED => self::IS_SAMPLED,
            ],
            $carrier
        );
    }

    public function test_extract_context_with_lowercase_headers(): void
    {
        $carrier = [
            'x-instana-t' => self::X_INSTANA_T,
            'x-instana-s' => self::X_INSTANA_S,
            'x-instana-l' => '1',
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::X_INSTANA_T, self::X_INSTANA_S, TraceFlags::SAMPLED),
            $this->getSpanContext($this->instanaMultiPropagator->extract($carrier))
        );
    }

    #[DataProvider('sampledValueProvider')]
    public function test_extract_sampled_context($sampledValue): void
    {
        $carrier = [
            $this->TRACE_ID => self::X_INSTANA_T,
            $this->SPAN_ID => self::X_INSTANA_S,
            $this->SAMPLED => $sampledValue,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::X_INSTANA_T, self::X_INSTANA_S, TraceFlags::SAMPLED),
            $this->getSpanContext($this->instanaMultiPropagator->extract($carrier))
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
            $this->TRACE_ID => self::X_INSTANA_T,
            $this->SPAN_ID => self::X_INSTANA_S,
            $this->SAMPLED => $sampledValue,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::X_INSTANA_T, self::X_INSTANA_S),
            $this->getSpanContext($this->instanaMultiPropagator->extract($carrier))
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

    #[DataProvider('DefaultSampledValueProvider')]
    public function test_extract_default_sampled_context($sampledValue): void
    {
        $carrier = [
            $this->TRACE_ID => self::X_INSTANA_T,
            $this->SPAN_ID => self::X_INSTANA_S,
            $this->SAMPLED => $sampledValue,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::X_INSTANA_T, self::X_INSTANA_S, TraceFlags::DEFAULT),
            $this->getSpanContext($this->instanaMultiPropagator->extract($carrier))
        );
    }

    public static function DefaultSampledValueProvider(): array
    {
        return [
            'null sampled value' => [null],
            'empty sampled value' => [[]],
        ];
    }

    #[DataProvider('InvalidSampledValueProvider')]
    public function test_extract_invalid_sampled_context($sampledValue): void
    {
        $carrier = [
            $this->TRACE_ID => self::X_INSTANA_T,
            $this->SPAN_ID => self::X_INSTANA_S,
            $this->SAMPLED => $sampledValue,
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::X_INSTANA_T, self::X_INSTANA_S),
            $this->getSpanContext($this->instanaMultiPropagator->extract($carrier))
        );
    }

    public static function InvalidSampledValueProvider(): array
    {
        return [
            'wrong sampled value 1' => ['wrong'],
            'wrong sampled value 2' => ['abcd'],
        ];
    }

    public function test_extract_context_with_sampled_no_trace_and_span_headers(): void
    {
        $carrier = [
            'X-INSTANA-L' => '1',
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(
                '00000000000000000000000000000000',
                '0000000000000000',
                TraceFlags::SAMPLED
            ),
            $this->getSpanContext($this->instanaMultiPropagator->extract($carrier))
        );
    }

    public function test_extract_context_with_no_trace_and_span_headers(): void
    {
        $carrier = [
            'X-INSTANA-L' => '0',
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent(
                '00000000000000000000000000000000',
                '0000000000000000',
                TraceFlags::DEFAULT
            ),
            $this->getSpanContext($this->instanaMultiPropagator->extract($carrier))
        );
    }

    public function test_extract_and_inject(): void
    {
        $extractCarrier = [
            $this->TRACE_ID => self::X_INSTANA_T,
            $this->SPAN_ID => self::X_INSTANA_S,
            $this->SAMPLED => self::IS_SAMPLED,
        ];
        $context = $this->instanaMultiPropagator->extract($extractCarrier);
        $injectCarrier = [];
        $this->instanaMultiPropagator->inject($injectCarrier, null, $context);
        $this->assertSame($injectCarrier, $extractCarrier);
    }

    public function test_extract_empty_trace_id(): void
    {
        $this->assertInvalid(
            [
                $this->TRACE_ID => '',
                $this->SPAN_ID => self::X_INSTANA_S,
                $this->SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    public function test_extract_leftpad_spand_id(): void
    {
        $carrier = [
            $this->TRACE_ID => '4aaba1a52cf8ee09',
            $this->SPAN_ID => '7b5a2e4d86bd1',
            $this->SAMPLED => '1',
        ];

        $this->assertEquals(
            SpanContext::createFromRemoteParent('00000000000000004aaba1a52cf8ee09', '0007b5a2e4d86bd1', TraceFlags::SAMPLED),
            $this->getSpanContext($this->instanaMultiPropagator->extract($carrier))
        );
    }

    public function test_invalid_trace_id(): void
    {
        $this->assertInvalid(
            [
                $this->TRACE_ID => 'abcdefghijklmnopabcdefghijklmnop',
                $this->SPAN_ID => self::X_INSTANA_S,
                $this->SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    public function test_invalid_trace_id_size(): void
    {
        $this->assertInvalid(
            [
                $this->TRACE_ID => self::X_INSTANA_T . '00',
                $this->SPAN_ID => self::X_INSTANA_S,
                $this->SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    public function test_extract_empty_span_id(): void
    {
        $this->assertInvalid(
            [
                $this->TRACE_ID => self::X_INSTANA_T,
                $this->SPAN_ID => '',
                $this->SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    public function test_invalid_span_id(): void
    {
        $this->assertInvalid(
            [
                $this->TRACE_ID => self::X_INSTANA_T,
                $this->SPAN_ID => 'abcdefghijklmnop',
                $this->SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    public function test_invalid_span_id_size(): void
    {
        $this->assertInvalid(
            [
                $this->TRACE_ID => self::X_INSTANA_T,
                $this->SPAN_ID => self::X_INSTANA_S . '00',
                $this->SAMPLED => self::IS_SAMPLED,
            ]
        );
    }

    private function assertInvalid(array $carrier): void
    {
        $this->assertSame(
            Context::getCurrent(),
            $this->instanaMultiPropagator->extract($carrier),
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
