<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Contrib\Jaeger\SpanConverter;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Trace\Event;
use OpenTelemetry\SDK\Trace\Link;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\Contrib\Jaeger\SpanConverter
 */
class JaegerSpanConverterTest extends TestCase
{
    public function test_should_convert_an_otel_span_to_a_jaeger_thrift_span()
    {
        $span = (new SpanData())
                    ->setName('otelSpanName');

        $jtSpan = (new SpanConverter())->convert($span);

        $this->assertSame(0, $jtSpan->traceIdLow);
        $this->assertSame(0, $jtSpan->traceIdHigh);
        $this->assertSame(0, $jtSpan->spanId);
        $this->assertSame(0, $jtSpan->parentSpanId);
        $this->assertSame('otelSpanName', $jtSpan->operationName);
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
            ->addAttribute('keyForArray', ['1stElement', '2ndElement', true])
            ->addAttribute('keyForInteger', 123)
            ->addAttribute('keyForFloat', 1.00);

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
        $this->assertTrue($jtSpan->tags[4]->vBool);

        $this->assertSame('keyForArray', $jtSpan->tags[5]->key);
        $this->assertSame('1stElement,2ndElement,true', $jtSpan->tags[5]->vStr);

        $this->assertSame('keyForInteger', $jtSpan->tags[6]->key);
        $this->assertSame(123, $jtSpan->tags[6]->vLong);

        $this->assertSame('keyForFloat', $jtSpan->tags[7]->key);
        $this->assertSame(1.00, $jtSpan->tags[7]->vDouble);
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

        $this->assertSame('error', $jtSpan->tags[0]->key);
        $this->assertTrue($jtSpan->tags[0]->vBool);
        $this->assertSame('otel.status_code', $jtSpan->tags[1]->key);
        $this->assertSame('ERROR', $jtSpan->tags[1]->vStr);
    }

    /**
     * @dataProvider provideSpanKindInputsAndExpectations
     */
    public function test_should_correctly_convert_span_kind_to_jaeger_thrift_tags(int $spanKindInput, string $expectedJaegerTagValue)
    {
        $span = (new SpanData())
            ->setKind($spanKindInput);

        $jtSpan = (new SpanConverter())->convert($span);

        $this->assertSame('span.kind', $jtSpan->tags[0]->key);
        $this->assertSame($expectedJaegerTagValue, $jtSpan->tags[0]->vStr);
    }

    public function provideSpanKindInputsAndExpectations()
    {
        yield [SpanKind::KIND_CLIENT, 'client'];
        yield [SpanKind::KIND_SERVER, 'server'];
        yield [SpanKind::KIND_CONSUMER, 'consumer'];
        yield [SpanKind::KIND_PRODUCER, 'producer'];
    }

    public function test_span_kind_internal_should_not_create_jaeger_thrift_tag()
    {
        $span = (new SpanData())
            ->setKind(SpanKind::KIND_INTERNAL);

        $jtSpan = (new SpanConverter())->convert($span);

        $this->assertCount(0, $jtSpan->tags);
    }

    public function test_should_correctly_convert_span_event_to_jaeger_log()
    {
        $span = (new SpanData())
                    ->setEvents(
                        [
                        new Event(
                            'eventName',
                            1505855794194009601,
                            new Attributes([
                                    'eventAttributeKey' => 'eventAttributeValue',
                                ])
                        ),
                        ]
                    );

        $jtSpan = (new SpanConverter())->convert($span);

        $this->assertSame(1505855794194009, $jtSpan->logs[0]->timestamp);

        $this->assertSame('eventAttributeKey', $jtSpan->logs[0]->fields[0]->key);
        $this->assertSame('eventAttributeValue', $jtSpan->logs[0]->fields[0]->vStr);
        $this->assertSame('event', $jtSpan->logs[0]->fields[1]->key);
        $this->assertSame('eventName', $jtSpan->logs[0]->fields[1]->vStr);
    }

    public function test_should_use_event_attribute_from_event_if_present_for_jaeger_log()
    {
        $span = (new SpanData())
                    ->setEvents(
                        [
                        new Event(
                            'eventName',
                            1505855794194009601,
                            new Attributes([
                                    'event' => 'valueForTheEventAttributeOnTheEvent',
                                ])
                        ),
                        ]
                    );

        $jtSpan = (new SpanConverter())->convert($span);

        $this->assertSame(1505855794194009, $jtSpan->logs[0]->timestamp);

        $this->assertSame('event', $jtSpan->logs[0]->fields[0]->key);
        $this->assertSame('valueForTheEventAttributeOnTheEvent', $jtSpan->logs[0]->fields[0]->vStr);
    }

    public function test_should_correctly_convert_span_link_to_jaeger_span_reference()
    {
        $span = (new SpanData())
                    ->setLinks(
                        [
                            new Link(
                                SpanContext::getInvalid()
                            ),
                        ]
                    );

        $jtSpan = (new SpanConverter())->convert($span);

        $this->assertSame(1, $jtSpan->references[0]->refType);
        $this->assertSame(0, $jtSpan->references[0]->traceIdLow);
        $this->assertSame(0, $jtSpan->references[0]->traceIdHigh);
        $this->assertSame(0, $jtSpan->references[0]->spanId);
    }
}
