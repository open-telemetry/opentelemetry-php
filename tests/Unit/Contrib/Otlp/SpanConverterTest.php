<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use Opentelemetry\Proto\Trace\V1\SpanFlags;
use function bin2hex;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\Contrib\Otlp\SpanConverter;
use Opentelemetry\Proto\Common\V1\AnyValue;
use Opentelemetry\Proto\Common\V1\ArrayValue;
use Opentelemetry\Proto\Common\V1\KeyValue;
use Opentelemetry\Proto\Resource\V1\Resource;
use Opentelemetry\Proto\Trace\V1;
use Opentelemetry\Proto\Trace\V1\ResourceSpans;
use Opentelemetry\Proto\Trace\V1\ScopeSpans;
use Opentelemetry\Proto\Trace\V1\Span\SpanKind as ProtoSpanKind;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpanConverter::class)]
class SpanConverterTest extends TestCase
{
    public function test_convert_span_to_payload(): void
    {
        $context = SpanContext::getInvalid();

        $span = (new SpanData())
            ->setName('batch.manager')
            ->addAttribute('attr1', 'apple')
            ->addAttribute('attr2', 'orange')
            ->addEvent('validators.list', Attributes::create(['job' => 'stage.updateTime']), 1505855799433901068)
            ->addLink($context, Attributes::create(['foo' => 'bar']))
            ->setHasEnded(true);

        $converter = new SpanConverter();
        /** @psalm-suppress InvalidArgument */
        $row = $converter->convert([$span])->getResourceSpans()[0]->getScopeSpans()[0]->getSpans()[0];

        $this->assertSame($span->getContext()->getSpanId(), bin2hex((string) $row->getSpanId()));
        $this->assertSame($span->getContext()->getTraceId(), bin2hex((string) $row->getTraceId()));
        $this->assertSame(V1\SpanFlags::SPAN_FLAGS_CONTEXT_HAS_IS_REMOTE_MASK, $row->getFlags());
        $this->assertSame($span->getName(), $row->getName());

        $this->assertCount(2, $row->getAttributes());
        $this->assertCount(1, $row->getLinks());

        /** @var V1\Span\Link $link */
        $link = $row->getLinks()[0];

        $this->assertSame($context->getTraceId(), bin2hex($link->getTraceId()));
        $this->assertSame($context->getSpanId(), bin2hex($link->getSpanId()));
        $this->assertSame(V1\SpanFlags::SPAN_FLAGS_CONTEXT_HAS_IS_REMOTE_MASK, $link->getFlags());
        $this->assertCount(1, $link->getAttributes());
    }

    public function test_span_context_is_remote_flags(): void
    {
        $span = (new SpanData())
            ->setParentContext(SpanContext::createFromRemoteParent('0000000000000001', '00000001'))
            ->setContext(SpanContext::create('0000000000000001', '00000002'))
            ->addLink(SpanContext::createFromRemoteParent('0000000000000001', '00000003'), Attributes::create([]))
            ->addLink(SpanContext::createFromRemoteParent('0000000000000001', '00000004', TraceFlags::SAMPLED), Attributes::create([]));

        $converter = new SpanConverter();
        /** @psalm-suppress InvalidArgument */
        $row = $converter->convert([$span])->getResourceSpans()[0]->getScopeSpans()[0]->getSpans()[0];

        $this->assertSame(V1\SpanFlags::SPAN_FLAGS_CONTEXT_HAS_IS_REMOTE_MASK | V1\SpanFlags::SPAN_FLAGS_CONTEXT_IS_REMOTE_MASK, $row->getFlags());
        $this->assertSame(V1\SpanFlags::SPAN_FLAGS_CONTEXT_HAS_IS_REMOTE_MASK | V1\SpanFlags::SPAN_FLAGS_CONTEXT_IS_REMOTE_MASK, $row->getLinks()[0]->getFlags());
        $this->assertSame(V1\SpanFlags::SPAN_FLAGS_CONTEXT_HAS_IS_REMOTE_MASK | V1\SpanFlags::SPAN_FLAGS_CONTEXT_IS_REMOTE_MASK | TraceFlags::SAMPLED, $row->getLinks()[1]->getFlags());
    }

    #[DataProvider('attributeAreCoercedCorrectlyDataProvider')]
    public function test_attribute_are_coerced_correctly($actual, $expected): void
    {
        $span = (new SpanData())
            ->setName('batch.manager')
            ->addAttribute('test.attribute', $actual);

        $converter = new SpanConverter();
        /** @psalm-suppress InvalidArgument */
        $converted = $converter->convert([$span])->getResourceSpans()[0];
        $attributes = $converted->getScopeSpans()[0]->getSpans()[0]->getAttributes();

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

    public static function attributeAreCoercedCorrectlyDataProvider(): array
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
        ];
    }

    public function test_otlp_happy_path_span(): void
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
                    Attributes::create([
                        'telemetry.sdk.name' => 'opentelemetry',
                        'telemetry.sdk.language' => 'php',
                        'telemetry.sdk.version' => 'dev',
                        'instance' => 'test-a',
                        'service.name' => 'unknown_service',
                    ])
                )
            )
            ->setStartEpochNanos($start_time)
            ->setEndEpochNanos($end_time)
            ->setName('http_get')
            ->setInstrumentationScope(new InstrumentationScope('lib-test', 'v0.1.0', 'http://url', Attributes::create(['foo' => 'bar'])))
            ->addAttribute('user', 'alice')
            ->addAttribute('authenticated', true)
            ->addEvent('Event1', Attributes::create(['success' => 'yes']), 1617313804325769955)
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
            'scope_spans' => [
                new ScopeSpans([
                    'scope' => new \Opentelemetry\Proto\Common\V1\InstrumentationScope([
                        'name' => 'lib-test',
                        'version' => 'v0.1.0',
                        'attributes' => [
                            new KeyValue([
                                'key' => 'foo',
                                'value' => new AnyValue(['string_value' => 'bar']),
                            ]),
                        ],
                    ]),
                    'spans' => [
                        new V1\Span([
                            'trace_id' => '0000000000000001',
                            'span_id' => '00000001',
                            'name' => 'http_get',
                            'start_time_unix_nano' => $start_time,
                            'end_time_unix_nano' => $end_time,
                            'kind' => V1\Span\SpanKind::SPAN_KIND_INTERNAL,
                            'flags' => SpanFlags::SPAN_FLAGS_CONTEXT_HAS_IS_REMOTE_MASK,
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
                    'schema_url' => 'http://url',
                ]),
            ],
        ]);

        $row = (new SpanConverter())->convert([$sdk])->getResourceSpans();

        /** @psalm-suppress InvalidArgument */
        $this->assertEquals($expected, $row[0]);
    }

    public function test_resources_from_multiple_spans_are_not_duplicated(): void
    {
        $span = $this->createMock(SpanData::class);
        $resource = $this->createMock(ResourceInfo::class);
        $attributes = Attributes::create(['foo' => 'foo', 'bar' => 'bar']);
        $span->method('getResource')->willReturn($resource);
        $resource->method('getAttributes')->willReturn($attributes);
        $converter = new SpanConverter();
        $result = $converter->convert([$span, $span, $span])->getResourceSpans();
        /** @psalm-suppress InvalidArgument */
        $this->assertCount(2, $result[0]->getResource()->getAttributes());
    }

    public function test_multiple_resources_result_in_multiple_resource_spans(): void
    {
        $resourceA = ResourceInfo::create(Attributes::create(['foo' => 'bar']));
        $resourceB = ResourceInfo::create(Attributes::create(['foo' => 'baz']));
        $converter = new SpanConverter();
        $result = $converter->convert([
            (new SpanData())->setResource($resourceA),
            (new SpanData())->setResource($resourceB),
        ])->getResourceSpans();
        $this->assertCount(2, $result);
    }

    public function test_otlp_no_spans(): void
    {
        $this->assertCount(0, (new SpanConverter())->convert([])->getResourceSpans());
    }

    #[DataProvider('spanKindProvider')]
    public function test_span_kind($kind, $expected): void
    {
        $span = (new SpanData())->setKind($kind);
        /** @psalm-suppress InvalidArgument */
        $row = (new SpanConverter())->convert([$span])->getResourceSpans()[0]->getScopeSpans()[0]->getSpans()[0];
        $this->assertSame($expected, $row->getKind());
    }

    public static function spanKindProvider(): array
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

    public function test_span_with_error_status(): void
    {
        $span = (new SpanData())->setStatus(StatusData::error());
        /** @psalm-suppress InvalidArgument */
        $row = (new SpanConverter())->convert([$span])->getResourceSpans()[0]->getScopeSpans()[0]->getSpans()[0];
        $this->assertSame(V1\Status\StatusCode::STATUS_CODE_ERROR, $row->getStatus()->getCode());
    }
}
