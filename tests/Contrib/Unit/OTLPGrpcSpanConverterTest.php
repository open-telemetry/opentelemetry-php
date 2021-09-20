<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use function bin2hex;
use OpenTelemetry\Contrib\OtlpGrpc\SpanConverter;
use Opentelemetry\Proto\Trace\V1;
use Opentelemetry\Proto\Trace\V1\InstrumentationLibrarySpans;
use Opentelemetry\Proto\Trace\V1\ResourceSpans;
use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Link;
use OpenTelemetry\Sdk\Trace\Links;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;

use OpenTelemetry\Sdk\Trace\SpanLimitsBuilder;
use OpenTelemetry\Sdk\Trace\SpanStatus;
use OpenTelemetry\Sdk\Trace\TracerProvider;

use OpenTelemetry\Trace\SpanKind;
use PHPUnit\Framework\TestCase;

class OTLPGrpcSpanConverterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldConvertASpanToAPayloadForOtlp(): void
    {
        $tracer = (new TracerProvider())->getTracer('OpenTelemetry.OtlpTest');

        $timestamp = Clock::get()->timestamp();

        $otherSpan = $tracer->startSpan('batch.manager');

        /** @var Span $span */
        $span = $tracer->startAndActivateSpan(
            'guard.validate',
            SpanKind::KIND_INTERNAL,
            new Attributes(['service' => 'guard']),
            new Links([new Link($otherSpan->getContext(), new Attributes(['foo' => 'bar']))])
        );
        $span->addEvent('validators.list', $timestamp, new Attributes(['job' => 'stage.updateTime']));
        $span->end();
        $otherSpan->end();

        $converter = new SpanConverter();
        $row = $converter->as_otlp_span($span);

        $this->assertInstanceOf(V1\Span::class, $row);

        $this->assertSame($span->getContext()->getSpanId(), bin2hex($row->getSpanId()));
        $this->assertSame($span->getContext()->getTraceId(), bin2hex($row->getTraceId()));

        $this->assertSame($span->getSpanName(), $row->getName());

        $this->assertCount(1, $row->getAttributes());
        $this->assertCount(1, $row->getLinks());

        /** @var V1\Span\Link $link */
        $link = $row->getLinks()[0];

        $this->assertSame($otherSpan->getContext()->getTraceId(), bin2hex($link->getTraceId()));
        $this->assertSame($otherSpan->getContext()->getSpanId(), bin2hex($link->getSpanId()));
        $this->assertCount(1, $link->getAttributes());

        // $this->assertIsInt($row['timestamp']);
        // // timestamp should be in microseconds
        // $this->assertGreaterThan(1e15, $row['timestamp']);

        // $this->assertIsInt($row['duration']);
        // $this->assertGreaterThan(0, $row['duration']);

        // $this->assertCount(1, $row['tags']);

        // /** @var Attribute $attribute */
        // $attribute = $span->getAttribute('service');
        // $this->assertEquals($attribute->getValue(), $row['tags']['service']);

        // $this->assertCount(1, $row['annotations']);
        // [$annotation] = $row['annotations'];
        // $this->assertEquals('validators.list', $annotation['value']);

        // [$event] = \iterator_to_array($span->getEvents());
        // $this->assertIsInt($annotation['timestamp']);

        // // timestamp should be in microseconds
        // $this->assertGreaterThan(1e15, $annotation['timestamp']);
    }

    /**
     * @test
     */
    // public function durationShouldBeInMicroseconds()
    // {
    //     $span = new Span('duration.test', SpanContext::generate());

    //     $row = (new SpanConverter('duration.test'))->as_otlp_span($span);

    //     $this->assertEquals(
    //         (int) (($span->getEnd() - $span->getStart()) / 1000),
    //         $row['duration']
    //     );
    // }

    /**
     * @test
     */
    public function tagsAreCoercedCorrectlyToStrings()
    {
        $span = new Span('tags.test', SpanContext::generate());

        $listOfStrings = ['string-1','string-2'];
        $listOfNumbers = [1,2,3,3.1415,42];
        $listOfBooleans = [true,true,false,true];
        $listOfRandoms = [true,[1,2,3],false,'string-1',3.1415];

        $span->setAttribute('string', 'string');
        $span->setAttribute('integer-1', 1024);
        $span->setAttribute('integer-2', 0);
        $span->setAttribute('float', '1.2345');
        $span->setAttribute('boolean-1', true);
        $span->setAttribute('boolean-2', false);
        $span->setAttribute('list-of-strings', $listOfStrings);
        $span->setAttribute('list-of-numbers', $listOfNumbers);
        $span->setAttribute('list-of-booleans', $listOfBooleans);
        $span->setAttribute('list-of-random', $listOfRandoms);

        $converter = new SpanConverter();
        $tags = $converter->as_otlp_span($span)->getAttributes();

        // // Check that we can convert all attributes to tags
        $this->assertCount(10, $tags);

        // // Tags destined for Otlp must be pairs of strings
        // foreach ($tags as $tagKey => $tagValue) {
        //     $this->assertIsString($tagKey);
        //     $this->assertIsString($tagValue);
        // }

        // $this->assertEquals($tags['string'], 'string');
        // $this->assertEquals($tags['integer-1'], '1024');
        // $this->assertEquals($tags['integer-2'], '0');
        // $this->assertEquals($tags['float'], '1.2345');
        // $this->assertEquals($tags['boolean-1'], 'true');
        // $this->assertEquals($tags['boolean-2'], 'false');

        // // Lists must be casted to strings and joined with a separator
        // $this->assertEquals($tags['list-of-strings'], join(',', $listOfStrings));
        // $this->assertEquals($tags['list-of-numbers'], join(',', $listOfNumbers));
        // $this->assertEquals($tags['list-of-booleans'], 'true,true,false,true');

        // // This currently works, but OpenTelemetry\Trace\Span should stop arrays
        // // containing multiple value types from being passed to the Exporter.
        // $this->assertEquals($tags['list-of-random'], 'true,1,2,3,false,string-1,3.1415');
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
            ResourceInfo::create(
                new Attributes([
                    'instance' => 'test-a',
                ])
            )
        );
        $sdk->setInstrumentationLibrary(new InstrumentationLibrary('lib-test', 'v0.1.0'));

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

    public function testOtlpNoSpans()
    {
        $spans = [];

        $otlpspan = (new SpanConverter())->as_otlp_resource_span($spans);

        $this->assertEquals(new ResourceSpans(), $otlpspan);
    }

    public function testOtlpDroppedAttributes()
    {
        $spanLimits = (new SpanLimitsBuilder())->setAttributeCountLimit(2)->build();
        $span = new Span('tags.test', SpanContext::generate(), null, null, SpanKind::KIND_INTERNAL, null, null, null, $spanLimits);

        $span->setAttribute('attr-1', '1');
        $span->setAttribute('attr-2', '2');
        $span->setAttribute('attr-3', '3');

        $converter = new SpanConverter();
        $convertedSpan = $converter->as_otlp_span($span);

        $this->assertCount(2, $convertedSpan->getAttributes());
        $this->assertEquals(1, $convertedSpan->getDroppedAttributesCount());
    }

    public function testOtlpDroppedEvents()
    {
        $spanLimits = (new SpanLimitsBuilder())->setEventCountLimit(2)->build();
        $span = new Span('tags.test', SpanContext::generate(), null, null, SpanKind::KIND_INTERNAL, null, null, null, $spanLimits);

        $span->addEvent('event-1', Clock::get()->timestamp());
        $span->addEvent('event-2', Clock::get()->timestamp());
        $span->addEvent('event-3', Clock::get()->timestamp());

        $converter = new SpanConverter();
        $convertedSpan = $converter->as_otlp_span($span);

        $this->assertCount(2, $convertedSpan->getEvents());
        $this->assertEquals(1, $convertedSpan->getDroppedEventsCount());
    }

    public function testOtlpDroppedLinks()
    {
        $spanLimits = (new SpanLimitsBuilder())->setLinkCountLimit(2)->build();
        $links = new Links([
            new Link(SpanContext::generate()),
            new Link(SpanContext::generate()),
            new Link(SpanContext::generate()),
        ]);

        $span = new Span('tags.test', SpanContext::generate(), null, null, SpanKind::KIND_INTERNAL, null, $links, null, $spanLimits);

        $converter = new SpanConverter();
        $convertedSpan = $converter->as_otlp_span($span);

        $this->assertCount(2, $convertedSpan->getLinks());
        $this->assertEquals(1, $convertedSpan->getDroppedLinksCount());
    }
}
