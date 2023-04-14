<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\Contrib\Otlp\ProtobufSerializer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Contrib\Otlp\ProtobufSerializer
 */
class ProtobufSerializerTest extends TestCase
{
    private const TRACE_ID = '197b4f650cd4fa2ecb0bbb883908bc72';
    private const SPAN_ID = '2fb80e00a3df9fd3';

    private SpanContextInterface $context;

    public function setUp(): void
    {
        $this->context = SpanContext::create(self::TRACE_ID, self::SPAN_ID);
    }

    public function test_binary_to_hex(): void
    {
        $encodedTraceId = base64_encode($this->context->getTraceIdBinary());
        $encodedSpanId = base64_encode($this->context->getSpanIdBinary());

        $this->assertSame($this->context->getTraceId(), ProtobufSerializer::base64BinaryToHex($encodedTraceId));
        $this->assertSame($this->context->getSpanId(), ProtobufSerializer::base64BinaryToHex($encodedSpanId));
    }

    public function test_fix_json(): void
    {
        $input = file_get_contents(__DIR__ . '/fixtures/trace.json');
        $expected = json_encode(json_decode(file_get_contents(__DIR__ . '/fixtures/trace-expected.json')));
        $fixed = ProtobufSerializer::fixJsonOutput($input);

        $this->assertJsonStringEqualsJsonString($expected, $fixed);
    }
}
