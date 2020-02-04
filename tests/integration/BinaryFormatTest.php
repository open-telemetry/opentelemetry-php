<?php

declare(strict_types=1);

namespace Propagation;

use OpenTelemetry\Context\SpanContext;
use OpenTelemetry\Propagation\BinaryFormat;
use PHPUnit\Framework\TestCase;

class BinaryFormatTest extends TestCase
{
    public function testValidSpanToBytes()
    {
        $serialized = (new BinaryFormat())
            ->toBytes(SpanContext::generate());
        $this->assertNotEmpty($serialized);
    }

    public function testEmptyFromBytes()
    {
        $span = (new BinaryFormat())->fromBytes('');
        $this->assertTrue($span->IsValid());
    }

    public function testOnlyTraceIdFromBytes()
    {
        $traceId = '10000000000000000000000000000000';
        $this->assertEquals($traceId, (new BinaryFormat())->fromBytes("00{$traceId}12")->getTraceId());
    }

    public function testValidSpanFromBytes()
    {
        $formatter = new BinaryFormat();
        $traceId = '10000000000000000000000000000000';
        $spanId = '1000000000000000';
        $span = new SpanContext($traceId, $spanId, 0, []);
        $serialized = $formatter->toBytes($span);

        $this->assertNotEmpty($serialized);
        $unserializedSpan = $formatter->fromBytes($serialized);
        $this->assertEquals($span->getTraceId(), $unserializedSpan->getTraceId());
        $this->assertEquals($span->getSpanId(), $unserializedSpan->getSpanId());
        $this->assertEquals($span->isSampled(), $unserializedSpan->isSampled());
        $this->assertNotEquals($span->isRemote(), $unserializedSpan->isRemote());
    }

    public function testValidSpanWithFlagFromBytes()
    {
        $formatter = new BinaryFormat();
        $traceId = '10000000000000000000000000000000';
        $spanId = '1000000000000000';
        $span = new SpanContext($traceId, $spanId, 1, []);
        $serialized = $formatter->toBytes($span);

        $this->assertNotEmpty($serialized);
        $unserializedSpan = $formatter->fromBytes($serialized);
        $this->assertEquals($span->getTraceId(), $unserializedSpan->getTraceId());
        $this->assertEquals($span->getSpanId(), $unserializedSpan->getSpanId());
        $this->assertEquals($span->isSampled(), $unserializedSpan->isSampled());
        $this->assertNotEquals($span->isRemote(), $unserializedSpan->isRemote());
    }
}
