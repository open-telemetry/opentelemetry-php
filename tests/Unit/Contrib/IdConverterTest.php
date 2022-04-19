<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\Contrib\Jaeger\IdConverter;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\Contrib\Jaeger\IdConverter
 */
class IdConverterTest extends TestCase
{
    //Based on this section of the Jaeger spec https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/trace/sdk_exporters/jaeger.md#ids
    public function test_correctly_converts_example_from_spec()
    {
        $hex = 'FF00000000000000';

        $this->assertEquals(-72057594037927936, IdConverter::convertOtelToJaegerSpanId($hex));
    }

    public function test_correctly_converts_random_span_id()
    {
        // 16 char hex string
        $hex = bin2hex(random_bytes(8));

        $this->assertEquals($hex, $this->dechexWithLeadingZeroes(IdConverter::convertOtelToJaegerSpanId($hex)));
    }

    public function test_correctly_converts_trace_id()
    {
        $hex = '0102030405060708090a0b0c0d0e0f10';

        $traceId = IdConverter::convertOtelToJaegerTraceIds($hex);

        $this->assertEquals(72623859790382856, $traceId['traceIdHigh']);
        $this->assertEquals(651345242494996240, $traceId['traceIdLow']);
    }

    public function test_correctly_converts_random_trace_id()
    {
        // 32 char hex string
        $hex = bin2hex(random_bytes(16));

        $traceId = IdConverter::convertOtelToJaegerTraceIds($hex);
        $convertedTraceId = $this->dechexWithLeadingZeroes($traceId['traceIdHigh']);
        $convertedTraceId .= $this->dechexWithLeadingZeroes($traceId['traceIdLow']);
        $this->assertEquals($hex, $convertedTraceId);
    }

    private function dechexWithLeadingZeroes(int $num)
    {
        return str_pad(dechex($num), 16, '0', STR_PAD_LEFT);
    }
}
