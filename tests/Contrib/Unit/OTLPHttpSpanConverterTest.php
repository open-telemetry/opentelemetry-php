<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\Contrib\OtlpHttp\SpanConverter;
use Opentelemetry\Proto\Trace\V1;
use Opentelemetry\Proto\Trace\V1\InstrumentationLibrarySpans;
use Opentelemetry\Proto\Trace\V1\ResourceSpans;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Span;

use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\SpanStatus;
use OpenTelemetry\Sdk\Trace\TracerProvider;

use PHPUnit\Framework\TestCase;

class OTLPhttpSpanConverterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldConvertASpanToAPayloadForOtlp()
    {
        $tracer = (new TracerProvider())->getTracer('OpenTelemetry.OtlpTest');

        $timestamp = Clock::get()->timestamp();

        /** @var Span $span */
        $span = $tracer->startAndActivateSpan('guard.validate');
        $span->setAttribute('attr1', 'apple');
        $span->setAttribute('attr2', 'orange');

        $span->addEvent('validators.list', $timestamp, new Attributes(['job' => 'stage.updateTime']));
        $span->end();

        $converter = new SpanConverter();
        $row = $converter->as_otlp_span($span);

        $this->assertInstanceOf(V1\Span::class, $row);

        $this->assertSame($span->getContext()->getSpanId(), bin2hex($row->getSpanId()));
        $this->assertSame($span->getContext()->getTraceId(), bin2hex($row->getTraceId()));

        $this->assertSame($span->getSpanName(), $row->getName());

        $this->assertEquals(2, $row->getAttributes()->count());
    }

    /**
     * @test
     * @dataProvider attributeAreCoercedCorrectlyDataProvider
     */
    public function attributeAreCoercedCorrectly($actual, $expected)
    {
        $span = new Span('attributes.test', SpanContext::generate());

        $span->setAttribute('test.attribute', $actual);

        $converter = new SpanConverter();
        $attributes = $converter->as_otlp_span($span)->getAttributes();

        // Check that we can convert all attributes to tags
        $this->assertCount(1, $attributes);

        $protoSpan = new V1\Span([
            'attributes' => [
                new \Opentelemetry\Proto\Common\V1\KeyValue([
                    'key' => 'test.attribute',
                    'value' => $expected,
                ]),
            ],
        ]);

        $this->assertEquals($protoSpan->getAttributes(), $attributes);
    }

    public function attributeAreCoercedCorrectlyDataProvider()
    {
        return [
            'String' => [
                'Pear', new \Opentelemetry\Proto\Common\V1\AnyValue([ 'string_value' => 'Pear']),
            ],
            'Int' => [
                1024, new \Opentelemetry\Proto\Common\V1\AnyValue(['int_value' => 1024]),
            ],
            'Float/Double' => [
                3.1415926535897932384626433832795028841971, new \Opentelemetry\Proto\Common\V1\AnyValue(['double_value' => 3.1415926535897932384626433832795028841971]),
            ],
            'Bool: true' => [
                true, new \Opentelemetry\Proto\Common\V1\AnyValue(['bool_value' => true]),
            ],
            'Bool: false' => [
                false, new \Opentelemetry\Proto\Common\V1\AnyValue(['bool_value' => false]),
            ],
        ];
    }

    public function testOtlpHappyPathSpan()
    {
        $start_time = 1617313804325769988;
        $end_time = 1617313804325783095;

        // Construct a comprehensive happy path Span in the SDK
        $sdk = new Span(
            'http_get',
            new SpanContext(
                bin2hex('0000000000000001'), // traceId
                bin2hex('00000001'), // spanId
                0, // traceFlags
            ),
            null, // parentSpanContext
            null, // sampler
            ResourceInfo::create(
                new Attributes([
                    'instance' => 'test-a',
                ])
            )
        );

        // We have to set the time twice here due to the way PHP deals with Monotonic Clock and Realtime Clock.
        $sdk->setStartEpochTimestamp($start_time);
        $sdk->setStart(125464959232828);

        $sdk->setAttribute('user', 'alice');
        $sdk->setAttribute('authenticated', true);

        $sdk->addEvent('Event1', 1617313804325769955, new Attributes(['success' => 'yes']));

        $sdk->setSpanStatus(SpanStatus::OK);

        $sdk->end(125464959245935);

        // Construct the same span in OTLP to compare.
        $expected = new ResourceSpans([
            'resource' => new \Opentelemetry\Proto\Resource\V1\Resource([
                'attributes' => [
                    new \Opentelemetry\Proto\Common\V1\KeyValue([
                        'key' => 'telemetry.sdk.name',
                        'value' => new \Opentelemetry\Proto\Common\V1\AnyValue([ 'string_value' => 'opentelemetry']),
                    ]),
                    new \Opentelemetry\Proto\Common\V1\KeyValue([
                        'key' => 'telemetry.sdk.language',
                        'value' => new \Opentelemetry\Proto\Common\V1\AnyValue([ 'string_value' => 'php']),
                    ]),
                    new \Opentelemetry\Proto\Common\V1\KeyValue([
                        'key' => 'telemetry.sdk.version',
                        'value' => new \Opentelemetry\Proto\Common\V1\AnyValue([ 'string_value' => 'dev']),
                    ]),
                    new \Opentelemetry\Proto\Common\V1\KeyValue([
                        'key' => 'instance',
                        'value' => new \Opentelemetry\Proto\Common\V1\AnyValue([ 'string_value' => 'test-a']),
                    ]),

                ],
            ]),
            'instrumentation_library_spans' => [
                new InstrumentationLibrarySpans([
                    'instrumentation_library' => new \Opentelemetry\Proto\Common\V1\InstrumentationLibrary([
                        // TODO: Fetch instrumentation library from TracerProvider
                        // 'name' => 'lib-a',
                        // 'version' => 'v0.1.0',
                    ]),
                    'spans' => [
                        new V1\Span([
                            'trace_id' => '0000000000000001',
                            'span_id' => '00000001',
                            'name' => 'http_get',
                            'start_time_unix_nano' => $start_time,
                            'end_time_unix_nano' => $end_time,
                            'kind' => V1\Span\SpanKind::SPAN_KIND_INTERNAL,
                            'status' => new V1\Status([ 'code' => V1\Status\StatusCode::STATUS_CODE_OK ]),
                            'attributes' => [
                                new \Opentelemetry\Proto\Common\V1\KeyValue([
                                    'key' => 'user',
                                    'value' => new \Opentelemetry\Proto\Common\V1\AnyValue([ 'string_value' => 'alice']),
                                ]),
                                new \Opentelemetry\Proto\Common\V1\KeyValue([
                                    'key' => 'authenticated',
                                    'value' => new \Opentelemetry\Proto\Common\V1\AnyValue([ 'bool_value' => true]),
                                ]),
                            ],
                            'events' => [
                                new V1\Span\Event([
                                    'name' => 'Event1',
                                    'time_unix_nano' => 1617313804325769955,
                                    'attributes' => [
                                        new \Opentelemetry\Proto\Common\V1\KeyValue([
                                            'key' => 'success',
                                            'value' => new \Opentelemetry\Proto\Common\V1\AnyValue([ 'string_value' => 'yes']),
                                        ]),
                                    ],
                                ]),
                            ],
                        ]),
                    ],
                ]),
            ],
        ]);

        $otlpspan = (new SpanConverter())->as_otlp_resource_span([$sdk]);

        $this->assertEquals($expected, $otlpspan);
    }
}
