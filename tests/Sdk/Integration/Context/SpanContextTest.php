<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Integration\Context;

use InvalidArgumentException;
use OpenTelemetry\Sdk\Trace\SpanContext;
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
        $this->expectExceptionMessageMatches($errorRegex);
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
        $this->assertTrue($spanContext->isValidContext());
    }

    public function testContextIsRemoteFromRestore(): void
    {
        $spanContext = SpanContext::restore('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', true, true);
        $this->assertTrue($spanContext->isRemoteContext());
    }

    public function testContextIsNotRemoteFromConstructor(): void
    {
        $spanContext = new SpanContext('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', 1);
        $this->assertFalse($spanContext->isRemoteContext());
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
        $this->assertTrue($spanContext->isValidContext());
        $this->assertFalse($spanContext->isSampled());
    }
}
