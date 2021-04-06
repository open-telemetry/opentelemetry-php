<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\Contrib\OtlpGrpc\SpanConverter;
use Opentelemetry\Proto\Trace\V1;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Sdk\Trace\Attribute;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Span;

use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\SpanStatus;
use OpenTelemetry\Sdk\Trace\TracerProvider;

use PHPUnit\Framework\TestCase;

class OTLPGrpcSpanConverterTest extends TestCase
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
        $span->setAttribute('service', 'guard');
        $span->addEvent('validators.list', $timestamp, new Attributes(['job' => 'stage.updateTime']));
        $span->end();

        $converter = new SpanConverter('test.name');
        $row = $converter->as_otlp_span($span);

        $this->assertInstanceOf(V1\Span::class, $row);

        $this->assertSame($span->getContext()->getSpanId(), bin2hex($row->getSpanId()));
        $this->assertSame($span->getContext()->getTraceId(), bin2hex($row->getTraceId()));

        $this->assertSame($span->getSpanName(), $row->getName());

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

        $converter = new SpanConverter('test.name');
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
        $start_time = 1617315085034507008;
        $end_time = 1617313804325769988;

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

        $sdk->setStartEpochTimestamp($start_time);

        $sdk->setAttribute('user', 'alice');
        $sdk->setAttribute('authenticated', true);

        $sdk->addEvent('Event1', 1617313804325769955, new Attributes(['sucess' => 'yes']));

        $sdk->setSpanStatus(SpanStatus::OK);

        $sdk->end($end_time); // Setting the end timestamp does not work how I expect it to

        // Construct the same span in OTLP to compare.
        $expected = new V1\Span([
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
                            'key' => 'sucess',
                            'value' => new \Opentelemetry\Proto\Common\V1\AnyValue([ 'string_value' => 'yes']),
                        ]),
                    ],
                ]),
            ],
        ]);

        $otlpspan = (new SpanConverter())->as_otlp_span($sdk);

        $this->assertEquals($expected, $otlpspan);
    }
}
