<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\Baggage;
use OpenTelemetry\Sdk\Trace\RandomIdGenerator;
use PHPUnit\Framework\TestCase;

class RandomIdGeneratorTest extends TestCase
{
    /**
     * @test
     */
    public function GeneratedTraceIdIsValid()
    {
        $idGenerator = new RandomIdGenerator();
        $traceId = $idGenerator->generateTraceId();

        $this->assertEquals(1, preg_match(Baggage::VALID_TRACE, $traceId));
    }

    /**
     * @test
     */
    public function generatedSpanIdIsValid()
    {
        $idGenerator = new RandomIdGenerator();
        $spanId = $idGenerator->generateSpanId();

        $this->assertEquals(1, preg_match(Baggage::VALID_SPAN, $spanId));
    }

    /**
     * @test
     */
    public function fallbackAlgorithm()
    {
        $idGenerator = new RandomIdGenerator();
        $reflection = new \ReflectionClass(RandomIdGenerator::class);
        $method = $reflection->getMethod('fallbackAlgorithm');
        $method->setAccessible(true);

        $traceId = $method->invokeArgs($idGenerator, [$reflection->getConstant('TRACE_ID_HEX_LENGTH')]);
        $this->assertEquals(1, preg_match(Baggage::VALID_TRACE, $traceId));

        $spanId = $method->invokeArgs($idGenerator, [$reflection->getConstant('SPAN_ID_HEX_LENGTH')]);
        $this->assertEquals(1, preg_match(Baggage::VALID_SPAN, $spanId));
    }
}
