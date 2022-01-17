<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Contrib\Jaeger\SpanConverter;
use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\Contrib\Jaeger\SpanConverter
 */
class JaegerSpanConverterTest extends TestCase
{
    public function test_should_convert_an_otlp_span_to_a_jaeger_thrift_span()
    {
        $span = (new SpanData())
                    ->setName('otlpSpanName');

        $jtSpan = (new SpanConverter())->convert($span);

        $this->assertSame(0, $jtSpan->traceIdLow);
        $this->assertSame(0, $jtSpan->traceIdHigh);
        $this->assertSame(0, $jtSpan->spanId);
        $this->assertSame(0, $jtSpan->parentSpanId);
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
                    StatusCode::STATUS_OK,
                    'status_description'
                )
            )
            ->setInstrumentationLibrary(new InstrumentationLibrary(
                'instrumentation_library_name',
                'instrumentation_library_version'
            ))
            ->addAttribute('keyForBoolean', true)
            ->addAttribute('keyForArray', ['1stElement', '2ndElement']);

        $jtSpan = (new SpanConverter())->convert($span);

        $this->assertSame('otel.status_code', $jtSpan->tags[0]->key);
        $this->assertSame('OK', $jtSpan->tags[0]->vStr);

        $this->assertSame('otel.status_description', $jtSpan->tags[1]->key);
        $this->assertSame('status_description', $jtSpan->tags[1]->vStr);

        $this->assertSame('otel.library.name', $jtSpan->tags[2]->key);
        $this->assertSame('instrumentation_library_name', $jtSpan->tags[2]->vStr);

        $this->assertSame('otel.library.version', $jtSpan->tags[3]->key);
        $this->assertSame('instrumentation_library_version', $jtSpan->tags[3]->vStr);

        $this->assertSame('keyForBoolean', $jtSpan->tags[4]->key);
        $this->assertSame('true', $jtSpan->tags[4]->vStr);

        $this->assertSame('keyForArray', $jtSpan->tags[5]->key);
        $this->assertSame('1stElement,2ndElement', $jtSpan->tags[5]->vStr);
    }

    public function test_should_correctly_convert_error_status_to_jaeger_thrift_tags()
    {
        $span = (new SpanData())
            ->setStatus(
                new StatusData(
                    StatusCode::STATUS_ERROR,
                    ''
                )
            );

        $jtSpan = (new SpanConverter())->convert($span);

        $this->assertSame('otel.status_code', $jtSpan->tags[0]->key);
        $this->assertSame('ERROR', $jtSpan->tags[0]->vStr);
    }
}
