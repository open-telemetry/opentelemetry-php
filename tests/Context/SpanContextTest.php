<?php

declare(strict_types=1);

namespace Context;

use InvalidArgumentException;
use OpenTelemetry\Context\SpanContext;
use PHPUnit\Framework\TestCase;

class SpanContextTest extends TestCase
{
    /**
     * @dataProvider invalidSpanData
     * @param string $traceID
     * @param string $spanID
     * @param string $errorRegex
     */
    public function testInvalidSpan(string $traceID, string $spanID, string $errorRegex): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp($errorRegex);
        SpanContext::restore($traceID, $spanID);
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
        $spanContext = SpanContext::restore('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', true);
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
        $state = ['a' => 'b'];
        $spanContext = new SpanContext($trace, $span, 0, $state);
        $this->assertSame($trace, $spanContext->getTraceId());
        $this->assertSame($span, $spanContext->getSpanId());
        $this->assertSame($state, $spanContext->getTraceState());
        $this->assertFalse($spanContext->isSampled());
    }

    public function testGenerateReturnsNonSampledValidContext()
    {
        $spanContext = SpanContext::generate();
        $this->assertTrue($spanContext->isValid());
        $this->assertFalse($spanContext->isSampled());
    }
}
