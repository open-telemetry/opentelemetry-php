<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use function bin2hex;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Contrib\Otlp\SpanConverter;
use Opentelemetry\Proto\Common\V1\AnyValue;
use Opentelemetry\Proto\Common\V1\ArrayValue;
use Opentelemetry\Proto\Common\V1\KeyValue;
use Opentelemetry\Proto\Resource\V1\Resource;
use Opentelemetry\Proto\Trace\V1;
use Opentelemetry\Proto\Trace\V1\InstrumentationLibrarySpans;
use Opentelemetry\Proto\Trace\V1\ResourceSpans;
use Opentelemetry\Proto\Trace\V1\Span\SpanKind as ProtoSpanKind;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\Tests\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

class OTLPSpanConverterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldConvertASpanToAPayloadForOtlp(): void
    {
        $context = SpanContext::getInvalid();

        $span = (new SpanData())
            ->setName('batch.manager')
            ->addAttribute('attr1', 'apple')
            ->addAttribute('attr2', 'orange')
            ->addEvent('validators.list', new Attributes(['job' => 'stage.updateTime']), 1505855799433901068)
            ->addLink($context, new Attributes(['foo' => 'bar']))
            ->setHasEnded(true);

        $converter = new SpanConverter();
        $row = $converter->convert([$span])[0]->getInstrumentationLibrarySpans()[0]->getSpans()[0];

        $this->assertSame($span->getContext()->getSpanId(), bin2hex($row->getSpanId()));
        $this->assertSame($span->getContext()->getTraceId(), bin2hex($row->getTraceId()));
        $this->assertSame($span->getName(), $row->getName());

        $this->assertCount(2, $row->getAttributes());
        $this->assertCount(1, $row->getLinks());

        /** @var V1\Span\Link $link */
        $link = $row->getLinks()[0];

        $this->assertSame($context->getTraceId(), bin2hex($link->getTraceId()));
        $this->assertSame($context->getSpanId(), bin2hex($link->getSpanId()));
        $this->assertCount(1, $link->getAttributes());
    }

    /**
     * @test
     * @dataProvider attributeAreCoercedCorrectlyDataProvider
     */
    public function attributeAreCoercedCorrectly($actual, $expected)
    {
        $span = (new SpanData())
            ->setName('batch.manager')
            ->addAttribute('test.attribute', $actual);

        $converter = new SpanConverter();
        $converted = $converter->convert([$span])[0];
        $attributes = $converted->getInstrumentationLibrarySpans()[0]->getSpans()[0]->getAttributes();

        // Check that we can convert all attributes to tags
        $this->assertCount(1, $attributes);

        $protoSpan = new V1\Span([
            'attributes' => [
                new KeyValue([
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
                'Pear', new AnyValue([ 'string_value' => 'Pear']),
            ],
            'Int' => [
                1024, new AnyValue(['int_value' => 1024]),
            ],
            'Float/Double' => [
                3.1415926535897932384626433832795028841971, new AnyValue(['double_value' => 3.1415926535897932384626433832795028841971]),
            ],
            'Bool: true' => [
                true, new AnyValue(['bool_value' => true]),
            ],
            'Bool: false' => [
                false, new AnyValue(['bool_value' => false]),
            ],
            'Array of Strings' => [
                ['string-1','string-2'],
                new AnyValue([
                    'array_value' => new ArrayValue([
                        'values' => [
                            new AnyValue(['string_value' => 'string-1']),
                            new AnyValue(['string_value' => 'string-2']),
                        ],
                    ]),
                ]),
            ],
            'Array of Randoms' => [
                ['Answer',42,true,['Nested Array']],
                new AnyValue([
                    'array_value' => new ArrayValue([
                        'values' => [
                            new AnyValue(['string_value' => 'Answer']),
                            new AnyValue(['int_value' => '42']),
                            new AnyValue(['bool_value' => true]),
                            new AnyValue([
                                'array_value' => new ArrayValue([
                                    'values' => [
                                        new AnyValue(['string_value' => 'Nested Array']),
                                    ],
                                ]),
                            ]),
                        ],
                    ]),
                ]),
            ],
        ];
    }

    public function testOtlpHappyPathSpan()
    {
        $start_time = 1617313804325769988;
        $end_time = 1617313804325783095;

        $sdk = (new SpanData())
            ->setContext(
                SpanContext::create(
                    bin2hex('0000000000000001'), // traceId
                    bin2hex('00000001'), // spanId
                    0, // traceFlags
                )
            )
            ->setResource(
                ResourceInfo::create(
                    new Attributes([
                        'instance' => 'test-a',
                    ])
                )
            )
            ->setStartEpochNanos($start_time)
            ->setEndEpochNanos($end_time)
            ->setName('http_get')
            ->setInstrumentationLibrary(new InstrumentationLibrary('lib-test', 'v0.1.0'))
            ->addAttribute('user', 'alice')
            ->addAttribute('authenticated', true)
            ->addEvent('Event1', new Attributes(['success' => 'yes']), 1617313804325769955)
            ->setStatus(StatusData::ok())
            ->setHasEnded(true);

        // Construct the same span in OTLP to compare.
        $expected = new ResourceSpans([
            'resource' => new Resource([
                'attributes' => [
                    new KeyValue([
                        'key' => 'telemetry.sdk.name',
                        'value' => new AnyValue([ 'string_value' => 'opentelemetry']),
                    ]),
                    new KeyValue([
                        'key' => 'telemetry.sdk.language',
                        'value' => new AnyValue([ 'string_value' => 'php']),
                    ]),
                    new KeyValue([
                        'key' => 'telemetry.sdk.version',
                        'value' => new AnyValue([ 'string_value' => 'dev']),
                    ]),
                    new KeyValue([
                        'key' => 'instance',
                        'value' => new AnyValue([ 'string_value' => 'test-a']),
                    ]),
                    new KeyValue([
                        'key' => 'service.name',
                        'value' => new AnyValue([ 'string_value' => 'unknown_service']),
                    ]),
                ],
            ]),
            'instrumentation_library_spans' => [
                new InstrumentationLibrarySpans([
                    'instrumentation_library' => new \Opentelemetry\Proto\Common\V1\InstrumentationLibrary([
                        'name' => 'lib-test',
                        'version' => 'v0.1.0',
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
                                new KeyValue([
                                    'key' => 'user',
                                    'value' => new AnyValue([ 'string_value' => 'alice']),
                                ]),
                                new KeyValue([
                                    'key' => 'authenticated',
                                    'value' => new AnyValue([ 'bool_value' => true]),
                                ]),
                            ],
                            'events' => [
                                new V1\Span\Event([
                                    'name' => 'Event1',
                                    'time_unix_nano' => 1617313804325769955,
                                    'attributes' => [
                                        new KeyValue([
                                            'key' => 'success',
                                            'value' => new AnyValue([ 'string_value' => 'yes']),
                                        ]),
                                    ],
                                ]),
                            ],
                            'dropped_attributes_count' => 0,
                            'dropped_events_count' => 0,
                            'dropped_links_count' => 0,
                        ]),
                    ],
                ]),
            ],
        ]);

        $row = (new SpanConverter())->convert([$sdk]);

        $this->assertEquals($expected, $row[0]);
    }

    /**
     * @covers OpenTelemetry\Contrib\Otlp\SpanConverter::as_otlp_resource_attributes
     */
    public function testResourcesFromMultipleSpansAreNotDuplicated()
    {
        $span = $this->createMock(SpanData::class);
        $resource = $this->createMock(ResourceInfo::class);
        $attributes = new Attributes(['foo' => 'foo', 'bar' => 'bar']);
        $span->method('getResource')->willReturn($resource);
        $resource->method('getAttributes')->willReturn($attributes);
        $converter = new SpanConverter();
        $result = $converter->convert([$span, $span, $span]);
        $this->assertCount(2, $result[0]->getResource()->getAttributes());
    }

    public function testOtlpNoSpans()
    {
        $spans = [];

        $row = (new SpanConverter())->convert($spans)[0];

        $this->assertEquals(new ResourceSpans(), $row);
    }

    /**
     * @dataProvider spanKindProvider
     * @covers OpenTelemetry\Contrib\Otlp\SpanConverter::as_otlp_span_kind
     */
    public function testSpanKind($kind, $expected): void
    {
        $span = (new SpanData())->setKind($kind);
        $row = (new SpanConverter())->convert([$span])[0]->getInstrumentationLibrarySpans()[0]->getSpans()[0];
        $this->assertSame($expected, $row->getKind());
    }

    public function spanKindProvider(): array
    {
        return [
            'internal' => [SpanKind::KIND_INTERNAL, ProtoSpanKind::SPAN_KIND_INTERNAL],
            'client' => [SpanKind::KIND_CLIENT, ProtoSpanKind::SPAN_KIND_CLIENT],
            'server' => [SpanKind::KIND_SERVER, ProtoSpanKind::SPAN_KIND_SERVER],
            'producer' => [SpanKind::KIND_PRODUCER, ProtoSpanKind::SPAN_KIND_PRODUCER],
            'consumer' => [SpanKind::KIND_CONSUMER, ProtoSpanKind::SPAN_KIND_CONSUMER],
            'undefined' => [999, ProtoSpanKind::SPAN_KIND_UNSPECIFIED],
        ];
    }

    /**
     * @covers OpenTelemetry\Contrib\Otlp\SpanConverter::as_otlp_span
     */
    public function testSpanWithErrorStatus(): void
    {
        $span = (new SpanData())->setStatus(StatusData::error());
        $row = (new SpanConverter())->convert([$span])[0]->getInstrumentationLibrarySpans()[0]->getSpans()[0];
        $this->assertSame(V1\Status\StatusCode::STATUS_CODE_ERROR, $row->getStatus()->getCode());
    }
}
