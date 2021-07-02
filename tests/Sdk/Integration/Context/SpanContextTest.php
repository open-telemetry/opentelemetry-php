<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Integration\Context;

use InvalidArgumentException;
use OpenTelemetry\Sdk\Trace\RandomIdGenerator;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\TraceState;
use PHPUnit\Framework\TestCase;

class SpanContextTest extends TestCase
{
    /**
     * @dataProvider invalidSpanData
     * @param string $traceID
     * @param string $spanID
     */
    public function testInvalidSpan(string $traceId, string $spanId): void
    {
        $spanContext = SpanContext::restore($traceId, $spanId);
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
        $spanContext = new SpanContext('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', 1);
        $this->assertTrue($spanContext->isValid());
    }

    public function testContextIsRemoteFromRestore(): void
    {
        $spanContext = SpanContext::restore('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', true, true);
        $this->assertTrue($spanContext->isRemote());
    }

    public function testContextIsNotRemoteFromConstructor(): void
    {
        $spanContext = new SpanContext('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', 1);
        $this->assertFalse($spanContext->isRemote());
    }

    public function testSampledSpan(): void
    {
        $spanContext = new SpanContext('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', 1);
        $this->assertTrue($spanContext->isSampled());
    }

    public function testGettersWork()
    {
        $trace = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $span = 'bbbbbbbbbbbbbbbb';
        $tracestate = new TraceState('a=b');
        $spanContext = new SpanContext($trace, $span, 0, $tracestate);
        $this->assertSame($trace, $spanContext->getTraceId());
        $this->assertSame($span, $spanContext->getSpanId());
        $this->assertSame($tracestate, $spanContext->getTraceState());
        $this->assertFalse($spanContext->isSampled());
    }

    public function testGenerateReturnsNonSampledValidContext()
    {
        $spanContext = SpanContext::generate();
        $this->assertTrue($spanContext->isValid());
        $this->assertFalse($spanContext->isSampled());
    }

    public function testRandomGeneratedIdsCreateValidContext()
    {
        $idGenerator = new RandomIdGenerator();
        $context = new SpanContext($idGenerator->generateTraceId(), $idGenerator->generateSpanId(), 0);
        $this->assertTrue($context->isValid());
    }
}
