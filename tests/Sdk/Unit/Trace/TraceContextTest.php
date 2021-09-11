<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\PropagationMap;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\TraceContextPropagator;
use OpenTelemetry\Sdk\Trace\TraceState;
use PHPUnit\Framework\TestCase;

class TraceContextTest extends TestCase
{
    private const VERSION = '00';
    private const TRACEID = '00000000000000000000000000000032';
    private const SPANID = '0000000000000016';
    private const SAMPLED = '01';
    private const TRACEPARENTVALUE = self::VERSION . '-' . self::TRACEID . '-' . self::SPANID . '-' . self::SAMPLED;

    /**
     * @test
     */
    public function testTraceContextFields()
    {
        $fields = TraceContextPropagator::fields();
        $this->assertSame($fields[0], TraceContextPropagator::TRACEPARENT);
        $this->assertSame($fields[1], TraceContextPropagator::TRACESTATE);
    }

    /**
     * @test
     */
    public function testExtractValidTraceparent()
    {
        $traceparentValues = [self::TRACEPARENTVALUE,
                              self::VERSION . '-' . self::TRACEID . '-' . self::SPANID . '-00', ];  // sampled == false

        foreach ($traceparentValues as $traceparentValue) {
            $carrier = [TraceContextPropagator::TRACEPARENT => $traceparentValue];
            $map = new PropagationMap();
            $context = TraceContextPropagator::extract($carrier, $map);
            $extractedTraceparent = '00-' . $context->getTraceId() . '-' . $context->getSpanId() . '-' . ($context->isSampled() ? '01' : '00');
            $this->assertSame($traceparentValue, $extractedTraceparent);
        }
    }

    /**
     * @test
     */
    public function testExtractValidTracestate()
    {
        $tracestateValues = ['vendor1=opaqueValue1',
                             'vendor2=opaqueValue2,vendor3=opaqueValue3', ];

        foreach ($tracestateValues as $tracestate) {
            $carrier = [TraceContextPropagator::TRACEPARENT => self::TRACEPARENTVALUE,
                        TraceContextPropagator::TRACESTATE => $tracestate, ];

            $map = new PropagationMap();
            $context = TraceContextPropagator::extract($carrier, $map);

            $this->assertSame($tracestate, (string) $context->getTraceState());
        }
    }

    /**
     * @test
     */
    public function testExtractCaseInsensitiveHeaders()
    {
        $tracestateValue = 'vendor2=opaqueValue2,vendor3=opaqueValue3';

        $carrier = ['TrAcEpArEnT' => self::TRACEPARENTVALUE,
                    'TrAcEsTaTe' => $tracestateValue, ];

        $map = new PropagationMap();
        $context = TraceContextPropagator::extract($carrier, $map);

        $extractedTraceparent = '00-' . $context->getTraceId() . '-' . $context->getSpanId() . '-' . ($context->isSampled() ? '01' : '00');
        $this->assertSame(self::TRACEPARENTVALUE, $extractedTraceparent);

        $this->assertSame($tracestateValue, (string) $context->getTraceState());
    }

    /**
     * @test
     */
    public function testExtractInvalidTraceparent()
    {
        $carrier = [];
        $map = new PropagationMap();

        $context = TraceContextPropagator::extract($carrier, $map);

        $this->assertSame(SpanContext::INVALID_TRACE, $context->getTraceId());
        $this->assertSame(SpanContext::INVALID_SPAN, $context->getSpanId());
        $this->assertSame(self::VERSION, ($context->isSampled() ? '01' : '00'));
        $this->assertFalse($context->isRemote());
    }

    /**
     * @test
     */
    public function testExtractInvalidTracestate()
    {
        // Tracestate with an invalid key
        $carrier = [TraceContextPropagator::TRACEPARENT => self::TRACEPARENTVALUE,
                    TraceContextPropagator::TRACESTATE => '@vendor1=opaqueValue1,vendor2=opaqueValue2', ];

        $map = new PropagationMap();
        $context = TraceContextPropagator::extract($carrier, $map);

        // Invalid list-member should be dropped
        $this->assertSame('vendor2=opaqueValue2', (string) $context->getTraceState());

        // Tracestate with an invalid value
        $carrier = [TraceContextPropagator::TRACEPARENT => self::TRACEPARENTVALUE,
                    TraceContextPropagator::TRACESTATE => 'vendor3=opaqueValue3,vendor4=' . chr(0x7F) . 'opaqueValue4', ];

        $map = new PropagationMap();
        $context = TraceContextPropagator::extract($carrier, $map);

        // Invalid list-member should be dropped
        $this->assertSame('vendor3=opaqueValue3', (string) $context->getTraceState());
    }

    /**
     * @test
     */
    public function testInjectValidTraceparent()
    {
        $carrier = [];
        $map = new PropagationMap();
        $context = SpanContext::restore(self::TRACEID, self::SPANID, true, false);
        TraceContextPropagator::inject($context, $carrier, $map);

        $this->assertSame(self::TRACEPARENTVALUE, $map->get($carrier, TraceContextPropagator::TRACEPARENT));
    }

    /**
     * @test
     */
    public function testInjectValidTracestate()
    {
        $carrier = [];
        $map = new PropagationMap();
        $tracestate = new TraceState('vendor1=opaqueValue1');
        $context = SpanContext::restore(self::TRACEID, self::SPANID, true, false, $tracestate);
        TraceContextPropagator::inject($context, $carrier, $map);

        $this->assertSame('vendor1=opaqueValue1', $map->get($carrier, TraceContextPropagator::TRACESTATE));
    }

    public function testInjectNullTracestate(): void
    {
        $carrier = [];
        $map = new PropagationMap();
        $context = SpanContext::restore(self::TRACEID, self::SPANID, true, false);
        TraceContextPropagator::inject($context, $carrier, $map);

        $this->assertNull($map->get($carrier, TraceContextPropagator::TRACESTATE));
    }

    public function testInjectEmptyTracestate(): void
    {
        $carrier = [];
        $map = new PropagationMap();
        $tracestate = new TraceState();
        $context = SpanContext::restore(self::TRACEID, self::SPANID, true, false, $tracestate);
        TraceContextPropagator::inject($context, $carrier, $map);

        $this->assertNull($map->get($carrier, TraceContextPropagator::TRACESTATE));
    }

    /**
     * @test
     */
    public function testInvalidTraceparentLength()
    {
        $invalidValues = [self::TRACEPARENTVALUE . '-extra',                            // Length > 4 values
                          self::VERSION . '-' . self::SPANID . '-' . self::SAMPLED, ];  // Length < 4 values

        foreach ($invalidValues as $invalidTraceparentValue) {
            $carrier = [TraceContextPropagator::TRACEPARENT => $invalidTraceparentValue];
            $map = new PropagationMap();

            $context = TraceContextPropagator::extract($carrier, $map);

            $this->assertSame(SpanContext::INVALID_TRACE, $context->getTraceId());
            $this->assertSame(SpanContext::INVALID_SPAN, $context->getSpanId());
            $this->assertSame(self::VERSION, ($context->isSampled() ? '01' : '00'));
            $this->assertFalse($context->isRemote());
        }
    }

    /**
     * @test
     */
    public function testInvalidTraceparentVersion()
    {
        $invalidValues = ['ff',     // invalid hex value
                          '003',    // Length > 2
                          '1',      // Length < 2
                          '0j', ];  // Hex character != 'a - f' or '0 - 9'

        $buildTraceparent = self::TRACEID . '-' . self::SPANID . '-' . self::SAMPLED;

        foreach ($invalidValues as $invalidVersion) {
            $traceparentValue = $invalidVersion . '-' . $buildTraceparent;
            $carrier = [TraceContextPropagator::TRACEPARENT => $traceparentValue];
            $map = new PropagationMap();

            $context = TraceContextPropagator::extract($carrier, $map);

            $this->assertSame(SpanContext::INVALID_TRACE, $context->getTraceId());
            $this->assertSame(SpanContext::INVALID_SPAN, $context->getSpanId());
            $this->assertSame(self::VERSION, ($context->isSampled() ? '01' : '00'));
            $this->assertFalse($context->isRemote());
        }
    }

    /**
     * @test
     */
    public function testInvalidTraceparentTraceId()
    {
        $invalidValues = ['00000000000000000000000000000000',     // All zeros
                          '000000000000000000000000000000033',    // Length > 32
                          '0000000000000000000000000000031',      // Length < 32
                          '000000000000000000000g0000000032', ];  // Hex character != 'a - f or 0 - 9'

        foreach ($invalidValues as $invalidTraceId) {
            $traceparentValue = self::VERSION . '-' . $invalidTraceId . '-' . self::SPANID . '-' . self::SAMPLED;
            $carrier = [TraceContextPropagator::TRACEPARENT => $traceparentValue];
            $map = new PropagationMap();

            $context = TraceContextPropagator::extract($carrier, $map);

            $this->assertSame(SpanContext::INVALID_TRACE, $context->getTraceId());
            $this->assertSame(SpanContext::INVALID_SPAN, $context->getSpanId());
            $this->assertSame(self::VERSION, ($context->isSampled() ? '01' : '00'));
            $this->assertFalse($context->isRemote());
        }
    }

    /**
     * @test
     */
    public function testInvalidTraceparentSpanId()
    {
        $invalidValues = ['0000000000000000',     // All zeros
                          '00000000000000017',    // Length > 16
                          '000000000000015',      // Length < 16
                          '00000000*0000016', ];  // Hex character != 'a - f or 0 - 9'

        foreach ($invalidValues as $invalidSpanId) {
            $traceparentValue = self::VERSION . '-' . self::TRACEID . '-' . $invalidSpanId . '-' . self::SAMPLED;
            $carrier = [TraceContextPropagator::TRACEPARENT => $traceparentValue];
            $map = new PropagationMap();

            $context = TraceContextPropagator::extract($carrier, $map);

            $this->assertSame(SpanContext::INVALID_TRACE, $context->getTraceId());
            $this->assertSame(SpanContext::INVALID_SPAN, $context->getSpanId());
            $this->assertSame(self::VERSION, ($context->isSampled() ? '01' : '00'));
            $this->assertFalse($context->isRemote());
        }
    }

    /**
     * @test
     */
    public function testInvalidTraceparentTraceFlags()
    {
        $invalidValues = ['003',    // Length > 2
                          '1',      // Length < 2
                          '0g', ];  // Hex character != 'a - f or 0 - 9'

        $buildTraceperent = self::VERSION . '-' . self::TRACEID . '-' . self::SPANID;
        foreach ($invalidValues as $invalidTraceFlag) {
            $traceparentValue = $buildTraceperent . '-' . $invalidTraceFlag;
            $carrier = [TraceContextPropagator::TRACEPARENT => $traceparentValue];
            $map = new PropagationMap();

            $context = TraceContextPropagator::extract($carrier, $map);

            $this->assertSame(SpanContext::INVALID_TRACE, $context->getTraceId());
            $this->assertSame(SpanContext::INVALID_SPAN, $context->getSpanId());
            $this->assertSame(self::VERSION, ($context->isSampled() ? '01' : '00'));
            $this->assertFalse($context->isRemote());
        }
    }
}
