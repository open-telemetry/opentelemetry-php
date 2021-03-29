<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\Contrib\Newrelic\SpanConverter;
use OpenTelemetry\Sdk\Trace\Attribute;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

class NewrelicSpanConverterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldConvertASpanToAPayloadForNewrelic()
    {
        $tracer = (new TracerProvider())->getTracer('OpenTelemetry.NewrelicTest');

        $timestamp = Clock::get()->timestamp();

        /** @var Span $span */
        $span = $tracer->startAndActivateSpan('guard.validate');
        $span->setAttribute('service', 'guard');
        $span->addEvent('validators.list', $timestamp, new Attributes(['job' => 'stage.updateTime']));
        $span->end();

        $converter = new SpanConverter('test.name');
        $row = $converter->convert($span);

        $this->assertSame($span->getContext()->getSpanId(), $row['id']);
        $this->assertSame($span->getContext()->getTraceId(), $row['trace.id']);

        $this->assertSame('test.name', $row['attributes']['service.name']);
        $this->assertSame($span->getSpanName(), $row['attributes']['name']);
        $this->assertNull($row['attributes']['parent.id']);
        $this->assertSame($span->getSpanName(), $row['attributes']['name']);

        $this->assertIsFloat($row['attributes']['timestamp']);
        // timestamp should be in milliseconds
        $this->assertGreaterThan(1e12, $row['attributes']['timestamp']);

        $this->assertIsFloat($row['attributes']['duration.ms']);
        $this->assertGreaterThan(0, $row['attributes']['duration.ms']);

        /** @var Attribute $attribute */
        $attribute = $span->getAttribute('service');
        $this->assertEquals($attribute->getValue(), $row['attributes']['service']);
    }

    /**
     * @test
     */
    public function durationShouldBeInMilliseconds()
    {
        $span = new Span('duration.test', SpanContext::generate());

        $row = (new SpanConverter('duration.test'))->convert($span);

        $this->assertEquals(
            (($span->getEnd() - $span->getStart()) / 1000000),
            $row['attributes']['duration.ms']
        );
    }

    /**
     * @test
     */
    public function attributesMaintainTypes()
    {
        $span = new Span('attributes.test', SpanContext::generate());

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

        $attributes = (new SpanConverter('tags.test'))->convert($span)['attributes'];

        // Check that we can convert all attributes to tags
        $this->assertCount(17, $attributes);

        // Attributes destined for Newrelic must be key/value pairs

        $this->assertEquals($attributes['string'], 'string');
        $this->assertEquals($attributes['integer-1'], 1024);
        $this->assertEquals($attributes['integer-2'], 0);
        $this->assertEquals($attributes['float'], 1.2345);
        $this->assertEquals($attributes['boolean-1'], true);
        $this->assertEquals($attributes['boolean-2'], false);

        // Lists are accepted
        $this->assertEquals($attributes['list-of-strings'], $listOfStrings);
        $this->assertEquals($attributes['list-of-numbers'], $listOfNumbers);
        $this->assertEquals($attributes['list-of-booleans'], $listOfBooleans);

        // This currently works, but OpenTelemetry\Trace\Span should stop arrays
        // containing multiple value types from being passed to the Exporter.
        $this->assertEquals($attributes['list-of-random'], $listOfRandoms);
    }
}
