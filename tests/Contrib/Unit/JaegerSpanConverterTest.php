<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Contrib\Jaeger\SpanConverter;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\Tests\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass SpanConverter
 */
class JaegerSpanConverterTest extends TestCase
{
    public function test_should_convert_an_otlp_span_to_a_jaeger_thrift_span()
    {
        $span = (new SpanData())
                    ->setName('otlpSpanName');    

        $jtSpan = (new SpanConverter('unused'))->convert($span);
 
        $this->assertSame('00000000000000000000000000000000', $jtSpan->traceIdLow);
        $this->assertSame(0, $jtSpan->traceIdHigh);
        $this->assertSame('0000000000000000', $jtSpan->spanId);
        $this->assertSame('0000000000000000', $jtSpan->parentSpanId);
        $this->assertSame('otlpSpanName', $jtSpan->operationName);
        $this->assertSame([], $jtSpan->references);
        $this->assertSame(0, $jtSpan->flags);
        $this->assertSame(1505855794194009, $jtSpan->startTime);
        $this->assertSame(5271716, $jtSpan->duration);
        //See test below for structure of $jtSpan->tags
        $this->assertSame([], $jtSpan->logs);
    }

    public function test_should_correctly_generate_jaeger_thrift_tags()
    {
        $span = (new SpanData())
            ->setStatus(
                new StatusData(
                    StatusCode::STATUS_ERROR,
                    'status_description'
                )
            )
            ->addAttribute('keyForBoolean', true)
            ->addAttribute('keyForArray', ['1stElement', '2ndElement']);

        $jtSpan = (new SpanConverter('unused'))->convert($span);

        $this->assertSame('op.status_code', $jtSpan->tags[0]->key);
        $this->assertSame('Error', $jtSpan->tags[0]->vStr);
        $this->assertSame('op.status_description', $jtSpan->tags[1]->key);
        $this->assertSame('status_description', $jtSpan->tags[1]->vStr);
        $this->assertSame('keyForBoolean', $jtSpan->tags[2]->key);
        $this->assertSame('true', $jtSpan->tags[2]->vStr);
        $this->assertSame('keyForArray', $jtSpan->tags[3]->key);
        $this->assertSame('1stElement,2ndElement', $jtSpan->tags[3]->vStr);
    }
}
