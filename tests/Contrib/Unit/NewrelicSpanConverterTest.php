<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\Contrib\Newrelic\SpanConverter;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\Tests\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

class NewrelicSpanConverterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldConvertASpanToAPayloadForNewrelic()
    {
        $span = (new SpanData())
            ->setName('guard.validate')
            ->addAttribute('service', 'guard')
            ->addEvent('validators.list', new Attributes(['job' => 'stage.updateTime']), 1505855799433901068)
            ->setHasEnded(true);

        $converter = new SpanConverter('test.name');
        $row = $converter->convert([$span])[0];

        $this->assertSame($span->getContext()->getSpanId(), $row['id']);
        $this->assertSame($span->getContext()->getTraceId(), $row['trace.id']);

        $this->assertSame('test.name', $row['attributes']['service.name']);
        $this->assertSame($span->getName(), $row['attributes']['name']);
        $this->assertNull($row['attributes']['parent.id']);
        $this->assertSame($span->getName(), $row['attributes']['name']);
        $this->assertSame(1505855794194, $row['attributes']['timestamp']);
        $this->assertIsFloat($row['attributes']['duration.ms']);
        $this->assertSame(5271.0, $row['attributes']['duration.ms']);

        $attribute = $span->getAttributes()->get('service');
        $this->assertEquals($attribute, $row['attributes']['service']);
    }

    /**
     * @test
     */
    public function attributesMaintainTypes()
    {
        $listOfStrings = ['string-1', 'string-2'];
        $listOfNumbers = [1, 2, 3, 3.1415, 42];
        $listOfBooleans = [true, true, false, true];
        $listOfRandoms = [true, [1, 2, 3], false, 'string-1', 3.1415];

        $span = (new SpanData())
            ->setName('attributes.test')
            ->addAttribute('string', 'string')
            ->addAttribute('integer-1', 1024)
            ->addAttribute('integer-2', 0)
            ->addAttribute('float', 1.2345)
            ->addAttribute('boolean-1', true)
            ->addAttribute('boolean-2', false)
            ->addAttribute('list-of-strings', $listOfStrings)
            ->addAttribute('list-of-numbers', $listOfNumbers)
            ->addAttribute('list-of-booleans', $listOfBooleans)
            ->addAttribute('list-of-random', $listOfRandoms);

        $attributes = (new SpanConverter('tags.test'))->convert([$span])[0]['attributes'];

        // Check that we can convert all attributes to tags
        $this->assertCount(17, $attributes);

        // Attributes destined for Newrelic must be key/value pairs

        $this->assertSame('string', $attributes['string']);
        $this->assertSame(1024, $attributes['integer-1']);
        $this->assertSame(0, $attributes['integer-2']);
        $this->assertSame(1.2345, $attributes['float']);
        $this->assertTrue($attributes['boolean-1']);
        $this->assertFalse($attributes['boolean-2']);

        // Lists are accepted
        $this->assertSame($listOfStrings, $attributes['list-of-strings']);
        $this->assertSame($listOfNumbers, $attributes['list-of-numbers']);
        $this->assertSame($listOfBooleans, $attributes['list-of-booleans']);

        // This currently works, but OpenTelemetry\Trace\Span should stop arrays
        // containing multiple value types from being passed to the Exporter.
        $this->assertSame($listOfRandoms, $attributes['list-of-random']);
    }
}
