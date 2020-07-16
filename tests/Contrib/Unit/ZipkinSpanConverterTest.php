<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\Contrib\Zipkin\SpanConverter;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

class ZipkinSpanConverterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldConvertASpanToAPayloadForZipkin()
    {
        $tracer = (new TracerProvider())->getTracer('OpenTelemetry.ZipkinTest');

        $timestamp = Clock::get()->timestamp();

        $span = $tracer->startAndActivateSpan('guard.validate');
        $span->setAttribute('service', 'guard');
        $span->addEvent('validators.list', $timestamp, new Attributes(['job' => 'stage.updateTime']));
        $span->end();

        $converter = new SpanConverter('test.name');
        $row = $converter->convert($span);

        $this->assertSame($span->getContext()->getSpanId(), $row['id']);
        $this->assertSame($span->getContext()->getTraceId(), $row['traceId']);

        $this->assertSame('test.name', $row['localEndpoint']['serviceName']);
        $this->assertSame($span->getSpanName(), $row['name']);

        $this->assertIsInt($row['timestamp']);
        // timestamp should be in microseconds
        $this->assertGreaterThan(1e15, $row['timestamp']);

        $this->assertIsInt($row['duration']);
        $this->assertGreaterThan(0, $row['duration']);

        $this->assertCount(1, $row['tags']);
        $this->assertEquals($span->getAttribute('service')->getValue(), $row['tags']['service']);

        $this->assertCount(1, $row['annotations']);
        [$annotation] = $row['annotations'];
        $this->assertEquals('validators.list', $annotation['value']);

        [$event] = \iterator_to_array($span->getEvents());
        $this->assertIsInt($annotation['timestamp']);

        // timestamp should be in microseconds
        $this->assertGreaterThan(1e15, $annotation['timestamp']);
    }

    /**
     * @test
     */
    public function durationShouldBeInMicroseconds()
    {
        $span = new Span('duration.test', SpanContext::generate());

        $row = (new SpanConverter('duration.test'))->convert($span);

        $this->assertEquals(
            (int) (($span->getEndTimestamp() - $span->getStartTimestamp()) / 1000),
            $row['duration']
        );
    }

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

        $tags = (new SpanConverter('tags.test'))->convert($span)['tags'];

        // Check that we can convert all attributes to tags
        $this->assertCount(10, $tags);

        // Tags destined for Zipkin must be pairs of strings
        foreach ($tags as $tagKey => $tagValue) {
            $this->assertIsString($tagKey);
            $this->assertIsString($tagValue);
        }

        $this->assertEquals($tags['string'], 'string');
        $this->assertEquals($tags['integer-1'], '1024');
        $this->assertEquals($tags['integer-2'], '0');
        $this->assertEquals($tags['float'], '1.2345');
        $this->assertEquals($tags['boolean-1'], 'true');
        $this->assertEquals($tags['boolean-2'], 'false');

        // Lists must be casted to strings and joined with a separator
        $this->assertEquals($tags['list-of-strings'], join(',', $listOfStrings));
        $this->assertEquals($tags['list-of-numbers'], join(',', $listOfNumbers));
        $this->assertEquals($tags['list-of-booleans'], 'true,true,false,true');

        // This currently works, but OpenTelemetry\Trace\Span should stop arrays
        // containing multiple value types from being passed to the Exporter.
        $this->assertEquals($tags['list-of-random'], 'true,1,2,3,false,string-1,3.1415');
    }
}
