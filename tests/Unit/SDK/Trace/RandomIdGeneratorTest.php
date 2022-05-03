<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\SDK\Trace\RandomIdGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Trace\RandomIdGenerator
 */
class RandomIdGeneratorTest extends TestCase
{
    /**
     * @group trace-compliance
     */
    public function test_generated_trace_id_is_valid(): void
    {
        $idGenerator = new RandomIdGenerator();
        $traceId = $idGenerator->generateTraceId();

        $this->assertEquals(1, preg_match(SpanContext::VALID_TRACE, $traceId));
    }

    /**
     * @group trace-compliance
     */
    public function test_generated_span_id_is_valid(): void
    {
        $idGenerator = new RandomIdGenerator();
        $spanId = $idGenerator->generateSpanId();

        $this->assertEquals(1, preg_match(SpanContext::VALID_SPAN, $spanId));
    }

    public function test_fallback_algorithm(): void
    {
        $idGenerator = new RandomIdGenerator();
        $reflection = new \ReflectionClass(RandomIdGenerator::class);
        $method = $reflection->getMethod('fallbackAlgorithm');
        $method->setAccessible(true);

        $traceId = $method->invokeArgs($idGenerator, [$reflection->getConstant('TRACE_ID_HEX_LENGTH')]);
        $this->assertEquals(1, preg_match(SpanContext::VALID_TRACE, $traceId));

        $spanId = $method->invokeArgs($idGenerator, [$reflection->getConstant('SPAN_ID_HEX_LENGTH')]);
        $this->assertEquals(1, preg_match(SpanContext::VALID_SPAN, $spanId));
    }
}
