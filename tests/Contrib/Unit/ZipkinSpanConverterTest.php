<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Contrib\Zipkin\SpanConverter;
use OpenTelemetry\SDK\Trace\Attribute;
use OpenTelemetry\SDK\Trace\Attributes;
use OpenTelemetry\Tests\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

class ZipkinSpanConverterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldConvertASpanToAPayloadForZipkin()
    {
        $span = (new SpanData())
            ->setName('guard.validate')
            ->addAttribute('service', 'guard')
            ->addEvent('validators.list', new Attributes(['job' => 'stage.updateTime']), 1505855799433901068)
            ->setHasEnded(true);

        $converter = new SpanConverter('test.name');
        $row = $converter->convert($span);

        $this->assertSame($span->getContext()->getSpanId(), $row['id']);
        $this->assertSame($span->getContext()->getTraceId(), $row['traceId']);

        $this->assertSame('test.name', $row['localEndpoint']['serviceName']);
        $this->assertSame($span->getName(), $row['name']);

        $this->assertSame(1505855794194009, $row['timestamp']);
        $this->assertSame(5271717, $row['duration']);

        $this->assertCount(3, $row['tags']);

        /** @var Attribute $attribute */
        $attribute = $span->getAttributes()->getAttribute('service');
        $this->assertSame($attribute->getValue(), $row['tags']['service']);

        $this->assertCount(1, $row['annotations']);
        [$annotation] = $row['annotations'];
        $this->assertSame('validators.list', $annotation['value']);
        $this->assertSame(1505855799433901, $annotation['timestamp']);
    }

    /**
     * @test
     */
    public function shouldConvertAnOTELServerSpanToAZipkinServerSpan()
    {
        $span = (new SpanData())
            ->setKind(SpanKind::KIND_SERVER);

        $converter = new SpanConverter('unused');
        $row = $converter->convert($span);

        $this->assertSame(SpanKind::KIND_SERVER, $row['kind']);
    }

    /**
     * @test
     */
    public function shouldConvertAnOTELClientSpanToAZipkinClientSpan()
    {
        $span = (new SpanData())
            ->setKind(SpanKind::KIND_CLIENT);

        $converter = new SpanConverter('unused');
        $row = $converter->convert($span);

        $this->assertSame(SpanKind::KIND_CLIENT, $row['kind']);
    }

    /**
     * @test
     */
    public function shouldConvertAnOTELProducerSpanToAZipkinProducerSpan()
    {
        $span = (new SpanData())
            ->setKind(SpanKind::KIND_PRODUCER);

        $converter = new SpanConverter('unused');
        $row = $converter->convert($span);

        $this->assertSame(SpanKind::KIND_PRODUCER, $row['kind']);
    }

    /**
     * @test
     */
    public function shouldConvertAnOTELConsumerSpanToAZipkinConsumerSpan()
    {
        $span = (new SpanData())
            ->setKind(SpanKind::KIND_CONSUMER);

        $converter = new SpanConverter('unused');
        $row = $converter->convert($span);

        $this->assertSame(SpanKind::KIND_CONSUMER, $row['kind']);
    }

    /**
     * @test
     */
    public function shouldConvertAnOTELInternalSpanToAZipkinSpanOfUnspecifiedKind()
    {
        $span = (new SpanData())
            ->setKind(SpanKind::KIND_INTERNAL);

        $converter = new SpanConverter('unused');
        $row = $converter->convert($span);

        $this->assertNull($row['kind']);
    }

    /**
     * @test
     */
    public function shouldConvertAnOTELSpanOfUnknownKindToAZipkinSpanOfUnspecifiedKind()
    {
        $span = (new SpanData())
            ->setKind(12345); //Some number not in the SpanKind "enum"

        $converter = new SpanConverter('unused');
        $row = $converter->convert($span);

        $this->assertNull($row['kind']);
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

        $tags = (new SpanConverter('tags.test'))->convert($span)['tags'];

        // Check that we can convert all attributes to tags
        $this->assertCount(12, $tags);

        // Tags destined for Zipkin must be pairs of strings
        foreach ($tags as $tagKey => $tagValue) {
            $this->assertIsString($tagKey);
            $this->assertIsString($tagValue);
        }

        $this->assertSame('string', $tags['string']);
        $this->assertSame('1024', $tags['integer-1']);
        $this->assertSame('0', $tags['integer-2']);
        $this->assertSame('1.2345', $tags['float']);
        $this->assertSame('true', $tags['boolean-1']);
        $this->assertSame('false', $tags['boolean-2']);

        // Lists must be casted to strings and joined with a separator
        $this->assertSame(implode(',', $listOfStrings), $tags['list-of-strings']);
        $this->assertSame(implode(',', $listOfNumbers), $tags['list-of-numbers']);
        $this->assertSame('true,true,false,true', $tags['list-of-booleans']);

        // This currently works, but OpenTelemetry\Trace\Span should stop arrays
        // containing multiple value types from being passed to the Exporter.
        $this->assertSame('true,1,2,3,false,string-1,3.1415', $tags['list-of-random']);
    }
}
