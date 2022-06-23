<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Contrib\Jaeger\SpanConverter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\Event;
use OpenTelemetry\SDK\Trace\Link;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\Contrib\Jaeger\SpanConverter
 * @covers OpenTelemetry\Contrib\Jaeger\IdConverter
 * @covers OpenTelemetry\Contrib\Jaeger\TagFactory\TagFactory
 */
class JaegerSpanConverterTest extends TestCase
{
    public function test_should_convert_an_otel_span_to_a_jaeger_thrift_span()
    {
        $span = (new SpanData())
            ->setName('otelSpanName');

        [$convertedSpan] = (new SpanConverter())->convert([$span]);

        $this->assertSame(0, $convertedSpan->traceIdLow);
        $this->assertSame(0, $convertedSpan->traceIdHigh);
        $this->assertSame(0, $convertedSpan->spanId);
        $this->assertSame(0, $convertedSpan->parentSpanId);
        $this->assertSame('otelSpanName', $convertedSpan->operationName);
        $this->assertSame([], $convertedSpan->references);
        $this->assertSame(0, $convertedSpan->flags);
        $this->assertSame(1505855794194009, $convertedSpan->startTime);
        $this->assertSame(5271716, $convertedSpan->duration);
        //See test below for structure of $convertedSpan->tags
        $this->assertSame([], $convertedSpan->logs);
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
            ->setInstrumentationScope(new InstrumentationScope(
                'instrumentation_scope_name',
                'instrumentation_scope_version'
            ))
            ->addAttribute('keyForBoolean', true)
            ->addAttribute('keyForArray', ['1stElement', '2ndElement', true])
            ->addAttribute('keyForInteger', 123)
            ->addAttribute('keyForFloat', 1.00)
            ->setResource(
                ResourceInfo::create(
                    Attributes::create([
                        'telemetry.sdk.name' => 'opentelemetry',
                        'telemetry.sdk.language' => 'php',
                        'telemetry.sdk.version' => 'dev',
                        'instance' => 'test-a',
                        'service.name' => 'unknown_service',
                    ])
                )
            );

        [$convertedSpan] = (new SpanConverter())->convert([$span]);

        $this->assertSame('otel.status_code', $convertedSpan->tags[0]->key);
        $this->assertSame('OK', $convertedSpan->tags[0]->vStr);

        $this->assertSame('otel.status_description', $convertedSpan->tags[1]->key);
        $this->assertSame('status_description', $convertedSpan->tags[1]->vStr);

        $this->assertSame('otel.scope.name', $convertedSpan->tags[2]->key);
        $this->assertSame('instrumentation_scope_name', $convertedSpan->tags[2]->vStr);

        $this->assertSame('otel.scope.version', $convertedSpan->tags[3]->key);
        $this->assertSame('instrumentation_scope_version', $convertedSpan->tags[3]->vStr);

        $this->assertSame('keyForBoolean', $convertedSpan->tags[4]->key);
        $this->assertTrue($convertedSpan->tags[4]->vBool);

        $this->assertSame('keyForArray', $convertedSpan->tags[5]->key);
        $this->assertSame('1stElement,2ndElement,true', $convertedSpan->tags[5]->vStr);

        $this->assertSame('keyForInteger', $convertedSpan->tags[6]->key);
        $this->assertSame(123, $convertedSpan->tags[6]->vLong);

        $this->assertSame('keyForFloat', $convertedSpan->tags[7]->key);
        $this->assertSame(1.00, $convertedSpan->tags[7]->vDouble);

        $this->assertSame('telemetry.sdk.name', $convertedSpan->tags[8]->key);
        $this->assertSame('opentelemetry', $convertedSpan->tags[8]->vStr);

        $this->assertSame('telemetry.sdk.language', $convertedSpan->tags[9]->key);
        $this->assertSame('php', $convertedSpan->tags[9]->vStr);

        $this->assertSame('telemetry.sdk.version', $convertedSpan->tags[10]->key);
        $this->assertSame('dev', $convertedSpan->tags[10]->vStr);

        $this->assertSame('instance', $convertedSpan->tags[11]->key);
        $this->assertSame('test-a', $convertedSpan->tags[11]->vStr);

        $this->assertSame('service.name', $convertedSpan->tags[12]->key);
        $this->assertSame('unknown_service', $convertedSpan->tags[12]->vStr);
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

        [$convertedSpan] = (new SpanConverter())->convert([$span]);

        $this->assertSame('error', $convertedSpan->tags[0]->key);
        $this->assertTrue($convertedSpan->tags[0]->vBool);
        $this->assertSame('otel.status_code', $convertedSpan->tags[1]->key);
        $this->assertSame('ERROR', $convertedSpan->tags[1]->vStr);
    }

    /**
     * @dataProvider provideSpanKindInputsAndExpectations
     */
    public function test_should_correctly_convert_span_kind_to_jaeger_thrift_tags(int $spanKindInput, string $expectedJaegerTagValue)
    {
        $span = (new SpanData())
            ->setKind($spanKindInput);

        [$convertedSpan] = (new SpanConverter())->convert([$span]);

        $this->assertSame('span.kind', $convertedSpan->tags[0]->key);
        $this->assertSame($expectedJaegerTagValue, $convertedSpan->tags[0]->vStr);
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

        [$convertedSpan] = (new SpanConverter())->convert([$span]);

        $this->assertCount(0, $convertedSpan->tags);
    }

    public function test_should_correctly_convert_span_event_to_jaeger_log()
    {
        $span = (new SpanData())
            ->setEvents(
                [
                    new Event(
                        'eventName',
                        1505855794194009601,
                        Attributes::create([
                            'eventAttributeKey' => 'eventAttributeValue',
                        ])
                    ),
                ]
            );

        [$convertedSpan] = (new SpanConverter())->convert([$span]);

        $this->assertSame(1505855794194009, $convertedSpan->logs[0]->timestamp);

        $this->assertSame('eventAttributeKey', $convertedSpan->logs[0]->fields[0]->key);
        $this->assertSame('eventAttributeValue', $convertedSpan->logs[0]->fields[0]->vStr);
        $this->assertSame('event', $convertedSpan->logs[0]->fields[1]->key);
        $this->assertSame('eventName', $convertedSpan->logs[0]->fields[1]->vStr);
    }

    public function test_should_use_event_attribute_from_event_if_present_for_jaeger_log()
    {
        $span = (new SpanData())
            ->setEvents(
                [
                    new Event(
                        'eventName',
                        1505855794194009601,
                        Attributes::create([
                            'event' => 'valueForTheEventAttributeOnTheEvent',
                        ])
                    ),
                ]
            );

        [$convertedSpan] = (new SpanConverter())->convert([$span]);

        $this->assertSame(1505855794194009, $convertedSpan->logs[0]->timestamp);

        $this->assertSame('event', $convertedSpan->logs[0]->fields[0]->key);
        $this->assertSame('valueForTheEventAttributeOnTheEvent', $convertedSpan->logs[0]->fields[0]->vStr);
    }

    public function test_should_correctly_convert_span_link_to_jaeger_span_reference()
    {
        $span = (new SpanData())
            ->setLinks(
                [
                    new Link(
                        SpanContext::getInvalid(),
                        Attributes::create([]),
                    ),
                ]
            );

        [$convertedSpan] = (new SpanConverter())->convert([$span]);

        $this->assertSame(1, $convertedSpan->references[0]->refType);
        $this->assertSame(0, $convertedSpan->references[0]->traceIdLow);
        $this->assertSame(0, $convertedSpan->references[0]->traceIdHigh);
        $this->assertSame(0, $convertedSpan->references[0]->spanId);
    }
}
