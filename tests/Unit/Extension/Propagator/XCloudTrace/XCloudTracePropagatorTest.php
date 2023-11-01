<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Extension\Propagator\XCloudTrace;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Extension\Propagator\XCloudTrace\XCloudTracePropagator;
use OpenTelemetry\SDK\Trace\Span;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Extension\Propagator\XCloudTrace\XCloudTracePropagator
 */
class XCloudTracePropagatorTest extends TestCase
{

    private const TRACE_ID_BASE16 = 'ff000000000000000000000000000041';
    private const SPAN_ID_BASE16 = '0000000000000013';
    private const SPAN_ID_BASE10 = '19';
    private const TRACE_ENABLED = 1;
    private const TRACE_DISABLED = 0;

    private string $xcloud;

    private TextMapPropagatorInterface $xCloudTracePropagator;

    protected function setUp(): void
    {
        $this->xCloudTracePropagator = XCloudTracePropagator::getInstance();
        [$this->xcloud] = $this->xCloudTracePropagator->fields();
    }

    public function test_fields(): void
    {
        $this->assertSame(
            ['x-cloud-trace-context'],
            $this->xCloudTracePropagator->fields()
        );
    }

    public function test_inject_invalid_context(): void
    {
        $carrier = [];
        $this
            ->xCloudTracePropagator
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
            ->xCloudTracePropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::SAMPLED),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [$this->xcloud => self::TRACE_ID_BASE16 . '/' . self::SPAN_ID_BASE10 . ';o=' . self::TRACE_ENABLED],
            $carrier
        );
    }

    public function test_inject_non_sampled_context(): void
    {
        $carrier = [];
        $this
            ->xCloudTracePropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::DEFAULT),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [$this->xcloud => self::TRACE_ID_BASE16 . '/' . self::SPAN_ID_BASE10 . ';o=' . self::TRACE_DISABLED],
            $carrier
        );
    }

    public function test_inject_non_sampled_default_context(): void
    {
        $carrier = [];
        $this
            ->xCloudTracePropagator
            ->inject(
                $carrier,
                null,
                $this->withSpanContext(
                    SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16),
                    Context::getCurrent()
                )
            );

        $this->assertSame(
            [$this->xcloud => self::TRACE_ID_BASE16 . '/' . self::SPAN_ID_BASE10 . ';o=' . self::TRACE_DISABLED],
            $carrier
        );
    }

    public function test_extract_nothing(): void
    {
        $this->assertSame(
            Context::getCurrent(),
            $this->xCloudTracePropagator->extract([])
        );
    }

    public function test_extract_sampled_context(): void
    {
        $carrier = [
            $this->xcloud => self::TRACE_ID_BASE16 . '/' . self::SPAN_ID_BASE10 . ';o=' . self::TRACE_ENABLED
        ];

        $context = $this->xCloudTracePropagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::SAMPLED),
            $this->getSpanContext($context)
        );
    }

    public function test_extract_non_sampled_context(): void
    {
        $carrier = [
            $this->xcloud => self::TRACE_ID_BASE16 . '/' . self::SPAN_ID_BASE10 . ';o=' . self::TRACE_DISABLED
        ];

        $context = $this->xCloudTracePropagator->extract($carrier);

        $this->assertEquals(
            SpanContext::createFromRemoteParent(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, TraceFlags::DEFAULT),
            $this->getSpanContext($context)
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
