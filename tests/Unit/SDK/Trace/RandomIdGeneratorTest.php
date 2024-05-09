<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\SDK\Trace\RandomIdGenerator;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Trace\RandomIdGenerator::class)]
class RandomIdGeneratorTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Group('trace-compliance')]
    public function test_generated_trace_id_is_valid(): void
    {
        $idGenerator = new RandomIdGenerator();
        $traceId = $idGenerator->generateTraceId();

        $this->assertEquals(1, preg_match(SpanContextValidator::VALID_TRACE, $traceId));
    }

    #[\PHPUnit\Framework\Attributes\Group('trace-compliance')]
    public function test_generated_span_id_is_valid(): void
    {
        $idGenerator = new RandomIdGenerator();
        $spanId = $idGenerator->generateSpanId();

        $this->assertEquals(1, preg_match(SpanContextValidator::VALID_SPAN, $spanId));
    }

    public function test_fallback_algorithm(): void
    {
        $idGenerator = new RandomIdGenerator();
        $reflection = new \ReflectionClass(RandomIdGenerator::class);
        $method = $reflection->getMethod('fallbackAlgorithm');
        $method->setAccessible(true);

        $traceId = $method->invokeArgs($idGenerator, [$reflection->getConstant('TRACE_ID_HEX_LENGTH')]);
        $this->assertEquals(1, preg_match(SpanContextValidator::VALID_TRACE, (string) $traceId));

        $spanId = $method->invokeArgs($idGenerator, [$reflection->getConstant('SPAN_ID_HEX_LENGTH')]);
        $this->assertEquals(1, preg_match(SpanContextValidator::VALID_SPAN, (string) $spanId));
    }
}
