<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Contrib\Zipkin\SpanConverter;
use OpenTelemetry\Contrib\Zipkin\SpanKind as ZipkinSpanKind;
use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Trace\Attribute;
use OpenTelemetry\SDK\Trace\Attributes;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\Tests\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

/**
 * @covers SpanConverter
 */
class ZipkinSpanConverterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldConvertASpanToAPayloadForZipkin()
    {
        $span = (new SpanData())
            ->setName('guard.validate')
            ->setKind(SpanKind::KIND_CLIENT)
            ->setParentContext(
                SpanContext::create(
                    '10000000000000000000000000000000',
                    '1000000000000000'
                )
            )
            ->setStatus(
                new StatusData(
                    StatusCode::STATUS_ERROR,
                    'status_description'
                )
            )
            ->setInstrumentationLibrary(new InstrumentationLibrary(
                'instrumentation_library_name',
                'instrumentation_library_version'
            ))
            ->addAttribute('service', 'guard')
            ->addAttribute('net.peer.name', 'authorizationservice.com')
            ->addAttribute('peer.service', 'AuthService')
            ->addEvent('validators.list', new Attributes(['job' => 'stage.updateTime']), 1505855799433901068)
            ->setHasEnded(true);

        $converter = new SpanConverter('test.name');
        $row = $converter->convert($span);

        $this->assertSame($span->getContext()->getSpanId(), $row['id']);
        $this->assertSame($span->getContext()->getTraceId(), $row['traceId']);
        $this->assertSame('1000000000000000', $row['parentId']);

        $this->assertSame('test.name', $row['localEndpoint']['serviceName']);
        $this->assertSame($span->getName(), $row['name']);

        $this->assertSame(1505855794194009, $row['timestamp']);
        $this->assertSame(5271717, $row['duration']);

        $this->assertCount(5, $row['tags']);

        $this->assertSame('Error', $row['tags']['otel.status_code']);
        $this->assertSame('status_description', $row['tags']['error']);

        $this->assertSame('instrumentation_library_name', $row['otel.library.name']);
        $this->assertSame('instrumentation_library_version', $row['otel.library.version']);

        /** @var Attribute $attribute */
        $attribute = $span->getAttributes()->getAttribute('service');
        $this->assertSame($attribute->getValue(), $row['tags']['service']);

        $this->assertCount(1, $row['annotations']);
        [$annotation] = $row['annotations'];
        $this->assertSame('"validators.list": {"job":"stage.updateTime"}', $annotation['value']);
        $this->assertSame(1505855799433901, $annotation['timestamp']);

        $this->assertSame('AuthService', $row['remoteEndpoint']['serviceName']);
    }

    /**
     * @test
     */
    public function shouldOmitEmptyKeysFromZipkinSpan()
    {
        $span = (new SpanData());

        $converter = new SpanConverter('unused');
        $row = $converter->convert($span);

        $this->assertArrayNotHasKey('kind', $row);
        $this->assertArrayNotHasKey('parentId', $row);
        $this->assertArrayNotHasKey('tags', $row);
        $this->assertArrayNotHasKey('otel.library.name', $row);
        $this->assertArrayNotHasKey('otel.library.version', $row);
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

        $this->assertSame(ZipkinSpanKind::SERVER, $row['kind']);
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

        $this->assertSame(ZipkinSpanKind::CLIENT, $row['kind']);
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

        $this->assertSame(ZipkinSpanKind::PRODUCER, $row['kind']);
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

        $this->assertSame(ZipkinSpanKind::CONSUMER, $row['kind']);
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

        $this->assertArrayNotHasKey('kind', $row);
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

        $this->assertArrayNotHasKey('kind', $row);
    }

    /**
     * @test
     */
    public function shouldConvertAnEventWithoutAttributesToAnAnnotationWithOnlyItsName()
    {
        $span = (new SpanData())
            ->addEvent('event.name', new Attributes());

        $converter = new SpanConverter('test.name');
        $row = $converter->convert($span);

        [$annotation] = $row['annotations'];
        $this->assertSame('"event.name"', $annotation['value']);
    }

    /**
     * @test
     */
    public function shouldUseOTELIpv4AndPortCorrectlyForZipkinRemoteEndpoint()
    {
        $span = (new SpanData())
            ->addAttribute('net.peer.ip', '255.255.255.255')
            ->addAttribute('net.peer.port', '80')
            ->setKind(SpanKind::KIND_PRODUCER);

        $converter = new SpanConverter('unused');
        $row = $converter->convert($span);

        $this->assertSame('unknown', $row['remoteEndpoint']['serviceName']);
        $this->assertSame(pow(2, 32)-1, $row['remoteEndpoint']['ipv4']);
        $this->assertSame(80, $row['remoteEndpoint']['port']);
    }

    /**
     * @test
     */
    public function shouldUseOTELIpv6CorrectlyForZipkinRemoteEndpoint()
    {
        $span = (new SpanData())
            ->addAttribute('net.peer.ip', '::1')
            ->setKind(SpanKind::KIND_PRODUCER);

        $converter = new SpanConverter('unused');
        $row = $converter->convert($span);

        $this->assertSame('00000000000000000000000000000001', bin2hex($row['remoteEndpoint']['ipv6'])); //Couldn't figure out how to do a direct assertion against binary data
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
        $this->assertCount(10, $tags);

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
