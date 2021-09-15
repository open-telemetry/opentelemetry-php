<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Trace\ArrayAccessGetterSetter;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\TraceContextPropagator;
use OpenTelemetry\Sdk\Trace\TraceState;
use PHPUnit\Framework\TestCase;

class TraceContextPropagatorTest extends TestCase
{
    private const VERSION = '00';
    private const TRACEID = '00000000000000000000000000000032';
    private const SPANID = '0000000000000016';
    private const SAMPLED = '01';
    private const TRACEPARENTVALUE = self::VERSION . '-' . self::TRACEID . '-' . self::SPANID . '-' . self::SAMPLED;

    /** @var callable */
    private $contextScope;

    protected function setUp(): void
    {
        $this->contextScope = Context::attach(new Context());
    }

    protected function tearDown(): void
    {
        Context::detach($this->contextScope);
    }

    public function testTraceContextFields(): void
    {
        $fields = TraceContextPropagator::fields();
        $this->assertSame($fields[0], TraceContextPropagator::TRACEPARENT);
        $this->assertSame($fields[1], TraceContextPropagator::TRACESTATE);
    }

    public function testExtractValidTraceparent(): void
    {
        $traceparentValues = [self::TRACEPARENTVALUE,
                              self::VERSION . '-' . self::TRACEID . '-' . self::SPANID . '-00', ];  // sampled == false

        foreach ($traceparentValues as $traceparentValue) {
            $carrier = [TraceContextPropagator::TRACEPARENT => $traceparentValue];
            $context = Span::fromContext(TraceContextPropagator::extract($carrier))->getContext();
            $extractedTraceparent = '00-' . $context->getTraceId() . '-' . $context->getSpanId() . '-' . ($context->isSampled() ? '01' : '00');
            $this->assertSame($traceparentValue, $extractedTraceparent);
        }
    }

    public function testExtractValidTracestate(): void
    {
        $tracestateValues = ['vendor1=opaqueValue1',
                             'vendor2=opaqueValue2,vendor3=opaqueValue3', ];

        foreach ($tracestateValues as $tracestate) {
            $carrier = [TraceContextPropagator::TRACEPARENT => self::TRACEPARENTVALUE,
                        TraceContextPropagator::TRACESTATE => $tracestate, ];

            $map = new ArrayAccessGetterSetter();
            $context = Span::fromContext(TraceContextPropagator::extract($carrier))->getContext();

            $this->assertSame($tracestate, (string) $context->getTraceState());
        }
    }

    public function testExtractCaseInsensitiveHeaders(): void
    {
        $tracestateValue = 'vendor2=opaqueValue2,vendor3=opaqueValue3';

        $carrier = ['TrAcEpArEnT' => self::TRACEPARENTVALUE,
                    'TrAcEsTaTe' => $tracestateValue, ];

        $context = Span::fromContext(TraceContextPropagator::extract($carrier))->getContext();

        $extractedTraceparent = '00-' . $context->getTraceId() . '-' . $context->getSpanId() . '-' . ($context->isSampled() ? '01' : '00');
        $this->assertSame(self::TRACEPARENTVALUE, $extractedTraceparent);

        $this->assertSame($tracestateValue, (string) $context->getTraceState());
    }

    public function testExtractInvalidTraceparent(): void
    {
        $carrier = [];

        $context = Span::fromContext(TraceContextPropagator::extract($carrier))->getContext();

        $this->assertSame(SpanContext::INVALID_TRACE, $context->getTraceId());
        $this->assertSame(SpanContext::INVALID_SPAN, $context->getSpanId());
        $this->assertSame(self::VERSION, ($context->isSampled() ? '01' : '00'));
        $this->assertFalse($context->isRemote());
    }

    public function testExtractInvalidTracestate(): void
    {
        // Tracestate with an invalid key
        $carrier = [TraceContextPropagator::TRACEPARENT => self::TRACEPARENTVALUE,
                    TraceContextPropagator::TRACESTATE => '@vendor1=opaqueValue1,vendor2=opaqueValue2', ];

        $context = Span::fromContext(TraceContextPropagator::extract($carrier))->getContext();

        // Invalid list-member should be dropped
        $this->assertSame('vendor2=opaqueValue2', (string) $context->getTraceState());

        // Tracestate with an invalid value
        $carrier = [TraceContextPropagator::TRACEPARENT => self::TRACEPARENTVALUE,
                    TraceContextPropagator::TRACESTATE => 'vendor3=opaqueValue3,vendor4=' . chr(0x7F) . 'opaqueValue4', ];

        $context = Span::fromContext(TraceContextPropagator::extract($carrier))->getContext();

        // Invalid list-member should be dropped
        $this->assertSame('vendor3=opaqueValue3', (string) $context->getTraceState());
    }

    public function testExtractInvalidTraceparentLength(): void
    {
        $invalidValues = [self::TRACEPARENTVALUE . '-extra',                            // Length > 4 values
            self::VERSION . '-' . self::SPANID . '-' . self::SAMPLED, ];  // Length < 4 values

        foreach ($invalidValues as $invalidTraceparentValue) {
            $carrier = [TraceContextPropagator::TRACEPARENT => $invalidTraceparentValue];

            $context = Span::fromContext(TraceContextPropagator::extract($carrier))->getContext();

            $this->assertSame(SpanContext::INVALID_TRACE, $context->getTraceId());
            $this->assertSame(SpanContext::INVALID_SPAN, $context->getSpanId());
            $this->assertSame(self::VERSION, ($context->isSampled() ? '01' : '00'));
            $this->assertFalse($context->isRemote());
        }
    }

    public function testExtractInvalidTraceparentVersion(): void
    {
        $invalidValues = ['ff',     // invalid hex value
            '003',    // Length > 2
            '1',      // Length < 2
            '0j', ];  // Hex character != 'a - f' or '0 - 9'

        $buildTraceparent = self::TRACEID . '-' . self::SPANID . '-' . self::SAMPLED;

        foreach ($invalidValues as $invalidVersion) {
            $traceparentValue = $invalidVersion . '-' . $buildTraceparent;
            $carrier = [TraceContextPropagator::TRACEPARENT => $traceparentValue];

            $context = Span::fromContext(TraceContextPropagator::extract($carrier))->getContext();

            $this->assertSame(SpanContext::INVALID_TRACE, $context->getTraceId());
            $this->assertSame(SpanContext::INVALID_SPAN, $context->getSpanId());
            $this->assertSame(self::VERSION, ($context->isSampled() ? '01' : '00'));
            $this->assertFalse($context->isRemote());
        }
    }

    public function testExtractInvalidTraceparentTraceId(): void
    {
        $invalidValues = ['00000000000000000000000000000000',     // All zeros
            '000000000000000000000000000000033',    // Length > 32
            '0000000000000000000000000000031',      // Length < 32
            '000000000000000000000g0000000032', ];  // Hex character != 'a - f or 0 - 9'

        foreach ($invalidValues as $invalidTraceId) {
            $traceparentValue = self::VERSION . '-' . $invalidTraceId . '-' . self::SPANID . '-' . self::SAMPLED;
            $carrier = [TraceContextPropagator::TRACEPARENT => $traceparentValue];

            $context = Span::fromContext(TraceContextPropagator::extract($carrier))->getContext();

            $this->assertSame(SpanContext::INVALID_TRACE, $context->getTraceId());
            $this->assertSame(SpanContext::INVALID_SPAN, $context->getSpanId());
            $this->assertSame(self::VERSION, ($context->isSampled() ? '01' : '00'));
            $this->assertFalse($context->isRemote());
        }
    }

    public function testExtractInvalidTraceparentSpanId(): void
    {
        $invalidValues = ['0000000000000000',     // All zeros
            '00000000000000017',    // Length > 16
            '000000000000015',      // Length < 16
            '00000000*0000016', ];  // Hex character != 'a - f or 0 - 9'

        foreach ($invalidValues as $invalidSpanId) {
            $traceparentValue = self::VERSION . '-' . self::TRACEID . '-' . $invalidSpanId . '-' . self::SAMPLED;
            $carrier = [TraceContextPropagator::TRACEPARENT => $traceparentValue];

            $context = Span::fromContext(TraceContextPropagator::extract($carrier))->getContext();

            $this->assertSame(SpanContext::INVALID_TRACE, $context->getTraceId());
            $this->assertSame(SpanContext::INVALID_SPAN, $context->getSpanId());
            $this->assertSame(self::VERSION, ($context->isSampled() ? '01' : '00'));
            $this->assertFalse($context->isRemote());
        }
    }

    public function testExtractInvalidTraceparentTraceFlags(): void
    {
        $invalidValues = ['003',    // Length > 2
            '1',      // Length < 2
            '0g', ];  // Hex character != 'a - f or 0 - 9'

        $buildTraceperent = self::VERSION . '-' . self::TRACEID . '-' . self::SPANID;
        foreach ($invalidValues as $invalidTraceFlag) {
            $traceparentValue = $buildTraceperent . '-' . $invalidTraceFlag;
            $carrier = [TraceContextPropagator::TRACEPARENT => $traceparentValue];

            $context = Span::fromContext(TraceContextPropagator::extract($carrier))->getContext();

            $this->assertSame(SpanContext::INVALID_TRACE, $context->getTraceId());
            $this->assertSame(SpanContext::INVALID_SPAN, $context->getSpanId());
            $this->assertSame(self::VERSION, ($context->isSampled() ? '01' : '00'));
            $this->assertFalse($context->isRemote());
        }
    }

    public function testInjectValidTraceparent(): void
    {
        $carrier = [];
        $map = new ArrayAccessGetterSetter();
        $context = (new Context())->withContextValue(Span::wrap(SpanContext::restore(self::TRACEID, self::SPANID, true, false)));
        TraceContextPropagator::inject($carrier, $map, $context);

        $this->assertSame(self::TRACEPARENTVALUE, $map->get($carrier, TraceContextPropagator::TRACEPARENT));
    }

    public function testInjectValidTracestate(): void
    {
        $carrier = [];
        $map = new ArrayAccessGetterSetter();
        $tracestate = new TraceState('vendor1=opaqueValue1');
        $context = (new Context())->withContextValue(Span::wrap(SpanContext::restore(self::TRACEID, self::SPANID, true, false, $tracestate)));

        TraceContextPropagator::inject($carrier, $map, $context);

        $this->assertSame('vendor1=opaqueValue1', $map->get($carrier, TraceContextPropagator::TRACESTATE));
    }

    public function testInjectNullTracestate(): void
    {
        $carrier = [];
        $map = new ArrayAccessGetterSetter();
        $context = (new Context())->withContextValue(Span::wrap(SpanContext::restore(self::TRACEID, self::SPANID, true, false)));
        TraceContextPropagator::inject($carrier, $map, $context);

        $this->assertNull($map->get($carrier, TraceContextPropagator::TRACESTATE));
    }

    public function testInjectEmptyTracestate(): void
    {
        $carrier = [];
        $map = new ArrayAccessGetterSetter();
        $tracestate = new TraceState();
        $context = (new Context())->withContextValue(Span::wrap(SpanContext::restore(self::TRACEID, self::SPANID, true, false, $tracestate)));
        TraceContextPropagator::inject($carrier, $map, $context);

        $this->assertNull($map->get($carrier, TraceContextPropagator::TRACESTATE));
    }
}
