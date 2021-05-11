<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Integration\Context;

use InvalidArgumentException;
use OpenTelemetry\Sdk\Trace\Baggage;
use OpenTelemetry\Sdk\Trace\RandomIdGenerator;
use OpenTelemetry\Sdk\Trace\TraceState;
use PHPUnit\Framework\TestCase;

class BaggageTest extends TestCase
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
        Baggage::restore($traceID, $spanID);
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
        $baggage = new Baggage('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', 1);
        $this->assertTrue($baggage->isValid());
    }

    public function testContextIsRemoteFromRestore(): void
    {
        $baggage = Baggage::restore('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', true, true);
        $this->assertTrue($baggage->isRemote());
    }

    public function testContextIsNotRemoteFromConstructor(): void
    {
        $baggage = new Baggage('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', 1);
        $this->assertFalse($baggage->isRemote());
    }

    public function testSampledSpan(): void
    {
        $baggage = new Baggage('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', 1);
        $this->assertTrue($baggage->isSampled());
    }

    public function testGettersWork()
    {
        $trace = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $span = 'bbbbbbbbbbbbbbbb';
        $tracestate = new TraceState('a=b');
        $baggage = new Baggage($trace, $span, 0, $tracestate);
        $this->assertSame($trace, $baggage->getTraceId());
        $this->assertSame($span, $baggage->getSpanId());
        $this->assertSame($tracestate, $baggage->getTraceState());
        $this->assertFalse($baggage->isSampled());
    }

    public function testGenerateReturnsNonSampledValidContext()
    {
        $baggage = Baggage::generate();
        $this->assertTrue($baggage->isValid());
        $this->assertFalse($baggage->isSampled());
    }

    public function testRandomGeneratedIdsCreateValidContext()
    {
        $idGenerator = new RandomIdGenerator();
        $context = new Baggage($idGenerator->generateTraceId(), $idGenerator->generateSpanId(), 0);
        $this->assertTrue($context->isValid());
    }
}
