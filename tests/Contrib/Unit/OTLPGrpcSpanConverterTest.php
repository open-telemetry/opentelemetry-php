<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use function bin2hex;
use OpenTelemetry\Contrib\OtlpGrpc\SpanConverter;
use Opentelemetry\Proto\Common\V1\AnyValue;
use Opentelemetry\Proto\Common\V1\KeyValue;
use Opentelemetry\Proto\Resource\V1\Resource;
use Opentelemetry\Proto\Trace\V1;
use Opentelemetry\Proto\Trace\V1\InstrumentationLibrarySpans;
use Opentelemetry\Proto\Trace\V1\ResourceSpans;
use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\Attributes;
use OpenTelemetry\SDK\Trace\SpanContext;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\SDK\Trace\Test\SpanData;
use PHPUnit\Framework\TestCase;

class OTLPGrpcSpanConverterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldConvertASpanToAPayloadForOtlp(): void
    {
        $context = SpanContext::getInvalid();

        $span = (new SpanData())
            ->setName('batch.manager')
            ->addAttribute('service', 'guard')
            ->addEvent('validators.list', new Attributes(['job' => 'stage.updateTime']), 1505855799433901068)
            ->addLink($context, new Attributes(['foo' => 'bar']))
            ->setHasEnded(true);

        $converter = new SpanConverter();
        $row = $converter->as_otlp_span($span);

        $this->assertSame($span->getContext()->getSpanId(), bin2hex($row->getSpanId()));
        $this->assertSame($span->getContext()->getTraceId(), bin2hex($row->getTraceId()));

        $this->assertSame($span->getName(), $row->getName());

        $this->assertCount(1, $row->getAttributes());
        $this->assertCount(1, $row->getLinks());

        /** @var V1\Span\Link $link */
        $link = $row->getLinks()[0];

        $this->assertSame($context->getTraceId(), bin2hex($link->getTraceId()));
        $this->assertSame($context->getSpanId(), bin2hex($link->getSpanId()));
        $this->assertCount(1, $link->getAttributes());

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
    public function tagsAreCoercedCorrectlyToStrings()
    {
        $listOfStrings = ['string-1', 'string-2'];
        $listOfNumbers = [1, 2, 3, 3.1415, 42];
        $listOfBooleans = [true, true, false, true];
        $listOfRandoms = [true, [1, 2, 3], false, 'string-1', 3.1415];

        $span = (new SpanData())
            ->setName('tags.test')
            ->addAttribute('string', 'string')
            ->addAttribute('integer-1', 1024)
            ->addAttribute('integer-2', 0)
            ->addAttribute('float', '1.2345')
            ->addAttribute('boolean-1', true)
            ->addAttribute('boolean-2', false)
            ->addAttribute('list-of-strings', $listOfStrings)
            ->addAttribute('list-of-numbers', $listOfNumbers)
            ->addAttribute('list-of-booleans', $listOfBooleans)
            ->addAttribute('list-of-random', $listOfRandoms);

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

        $otlpspan = (new SpanConverter())->as_otlp_resource_span([$sdk]);

        $this->assertEquals($expected, $otlpspan);
    }

    public function testOtlpNoSpans()
    {
        $spans = [];

        $otlpspan = (new SpanConverter())->as_otlp_resource_span($spans);

        $this->assertEquals(new ResourceSpans(), $otlpspan);
    }
}
