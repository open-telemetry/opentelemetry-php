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

    public function test_correctly_converted_span_id()
    {
        // 16 char hex string
        $hex = bin2hex(random_bytes(8));

        $this->assertEquals($hex, dechex(IdConverter::convertOtelToJaegerSpanId($hex)));
    }

    public function test_correctly_converted_trace_id()
    {
        // 32 char hex string
        $hex = bin2hex(random_bytes(16));

        $traceId = IdConverter::convertOtelToJaegerTraceIds($hex);
        $convertedTraceId = dechex((int) $traceId['traceIdHigh']);
        $convertedTraceId .= dechex((int) $traceId['traceIdLow']);
        $this->assertEquals($hex, $convertedTraceId);
    }
}
