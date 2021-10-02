<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Integration\Context;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Trace\RandomIdGenerator;
use OpenTelemetry\SDK\Trace\SpanContext;
use OpenTelemetry\SDK\Trace\TraceState;
use PHPUnit\Framework\TestCase;

class SpanContextTest extends TestCase
{
    /**
     * @dataProvider invalidSpanData
     * @param string $traceId
     * @param string $spanId
     */
    public function testInvalidSpan(string $traceId, string $spanId): void
    {
        $spanContext = SpanContext::create($traceId, $spanId);
        $this->assertSame(SpanContext::INVALID_TRACE, $spanContext->getTraceId());
        $this->assertSame(SpanContext::INVALID_SPAN, $spanContext->getSpanId());
    }

    public function invalidSpanData(): array
    {
        return [
            // Too long TraceID
            ['aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa36', 'bbbbbbbbbbbbbb16', '/^TraceID/'],
            // Too long SpanID
            ['aaaaaaaaaaaaaaaaaaaaaaaaaaaaaa32', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbb32', '/^SpanID/'],
            // Bad characters in TraceID
            ['bad trace', 'bbbbbbbbbbbbbb16', '/^TraceID/'],
            // Bad characters in SpanID
            ['aaaaaaaaaaaaaaaaaaaaaaaaaaaaaa32', 'bad span', '/^SpanID/'],
        ];
    }

    public function testValidSpan(): void
    {
        $spanContext = SpanContext::create('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', API\SpanContextInterface::TRACE_FLAG_SAMPLED);
        $this->assertTrue($spanContext->isValid());
    }

    public function testContextIsRemoteFromRestore(): void
    {
        $spanContext = SpanContext::createFromRemoteParent('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', API\SpanContextInterface::TRACE_FLAG_SAMPLED);
        $this->assertTrue($spanContext->isRemote());
    }

    public function testContextIsNotRemoteFromConstructor(): void
    {
        $spanContext = SpanContext::create('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', API\SpanContextInterface::TRACE_FLAG_SAMPLED);
        $this->assertFalse($spanContext->isRemote());
    }

    public function testSampledSpan(): void
    {
        $spanContext = SpanContext::create('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', API\SpanContextInterface::TRACE_FLAG_SAMPLED);
        $this->assertTrue($spanContext->isSampled());
    }

    public function testGettersWork()
    {
        $trace = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $span = 'bbbbbbbbbbbbbbbb';
        $tracestate = new TraceState('a=b');
        $spanContext = SpanContext::create($trace, $span, API\SpanContextInterface::TRACE_FLAG_DEFAULT, $tracestate);
        $this->assertSame($trace, $spanContext->getTraceId());
        $this->assertSame($span, $spanContext->getSpanId());
        $this->assertSame($tracestate, $spanContext->getTraceState());
        $this->assertFalse($spanContext->isSampled());
    }

    public function testRandomGeneratedIdsCreateValidContext()
    {
        $idGenerator = new RandomIdGenerator();
        $context = SpanContext::create($idGenerator->generateTraceId(), $idGenerator->generateSpanId(), 0);
        $this->assertTrue($context->isValid());
    }
}
