<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\PropagationMap;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\TraceContext;
use PHPUnit\Framework\TestCase;

class TraceContextTest extends TestCase
{
    private const VERSION = '00';
    private const TRACEID = '00000000000000000000000000000032';
    private const SPANID = '0000000000000016';
    private const SAMPLED = '01';
    private const TRACEPARENTVALUE = self::VERSION.'-'.self::TRACEID.'-'.self::SPANID.'-'.self::SAMPLED;

    /**
     * @test
     */
    public function testExtractValidTraceContext()
    {
        $traceparentValues = [self::TRACEPARENTVALUE,                                     // sampled == true
                              self::VERSION.'-'.self::TRACEID.'-'.self::SPANID.'-00', ];  // sampled == false

        foreach ($traceparentValues as $traceparentValue) {
            $carrier = [TraceContext::TRACEPARENT => $traceparentValue];
            $map = new PropagationMap();
            $context = TraceContext::extract($carrier, $map);
            $extractedTraceparent = '00-'.$context->getTraceId().'-'.$context->getSpanId().'-'.($context->isSampled() ? '01' : '00');
            $this->assertSame($traceparentValue, $extractedTraceparent);
        }
    }

    /**
     * @test
     */
    public function testInjectValidTraceContext()
    {
        $carrier = [];
        $map = new PropagationMap();
        $context = SpanContext::restore(self::TRACEID, self::SPANID, true, false);
        TraceContext::inject($context, $carrier, $map);

        $this->assertSame(self::TRACEPARENTVALUE, $map->get($carrier, TraceContext::TRACEPARENT));
    }

    /**
     * @test
     */
    public function testTraceparentLength()
    {
        $invalidValues = [self::TRACEPARENTVALUE.'-extra',                      // Length > 4 values
                          self::VERSION.'-'.self::SPANID.'-'.self::SAMPLED, ];  // Length < 4 values

        foreach ($invalidValues as $invalidTraceparentValue) {
            $carrier = [TraceContext::TRACEPARENT => $invalidTraceparentValue];
            $map = new PropagationMap();

            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Unable to extract traceparent. Contains Invalid values');
            $context = TraceContext::extract($carrier, $map);
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
                          '0j', ];  // Hex character != 'a - f or 0 - 9'

        $buildTraceparent = self::TRACEID.'-'.self::SPANID.'-'.self::SAMPLED;

        foreach ($invalidValues as $invalidVersion) {
            $traceparentValue = $invalidVersion.'-'.$buildTraceparent;
            $carrier = [TraceContext::TRACEPARENT => $traceparentValue];
            $map = new PropagationMap();

            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Only version 00 is supported, got '.$invalidVersion);
            $context = TraceContext::extract($carrier, $map);
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
            $traceparentValue = self::VERSION.'-'.$invalidTraceId.'-'.self::SPANID.'-'.self::SAMPLED;
            $carrier = [TraceContext::TRACEPARENT => $traceparentValue];
            $map = new PropagationMap();

            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('TraceID must be exactly 16 bytes (32 chars) and at least one non-zero byte, got '.$invalidTraceId);
            $context = TraceContext::extract($carrier, $map);
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
            $traceparentValue = self::VERSION.'-'.self::TRACEID.'-'.$invalidSpanId.'-'.self::SAMPLED;
            $carrier = [TraceContext::TRACEPARENT => $traceparentValue];
            $map = new PropagationMap();

            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('SpanID must be exactly 8 bytes (16 chars) and at least one non-zero byte, got '.$invalidSpanId);
            $context = TraceContext::extract($carrier, $map);
        }
    }

    /**
     * @test
     */
    public function testInvalidTraceFlags()
    {
        $invalidValues = ['003',    // Length > 2
                          '1',      // Length < 2
                          '0g', ];  // Hex character != 'a - f or 0 - 9'

        $buildTraceperent = self::VERSION.'-'.self::TRACEID.'-'.self::SPANID;
        foreach ($invalidValues as $invalidTraceFlag) {
            $traceparentValue = $buildTraceperent.'-'.$invalidTraceFlag;
            $carrier = [TraceContext::TRACEPARENT => $traceparentValue];
            $map = new PropagationMap();

            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('TraceFlags must be exactly 1 bytes (1 char) representing a bit field, got '.$invalidTraceFlag);
            $context = TraceContext::extract($carrier, $map);
        }
    }
}
