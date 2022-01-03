<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Contrib\Zipkin\SpanConverter;
use OpenTelemetry\Contrib\Zipkin\SpanKind as ZipkinSpanKind;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\Tests\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass SpanConverter
 */
class ZipkinSpanConverterTest extends TestCase
{
    public function test_should_convert_a_span_to_a_payload_for_zipkin(): void
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
        $row = $converter->convert([$span])[0];

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

        $attribute = $span->getAttributes()->get('service');
        $this->assertSame($attribute, $row['tags']['service']);

        $this->assertCount(1, $row['annotations']);
        [$annotation] = $row['annotations'];
        $this->assertSame('"validators.list": {"job":"stage.updateTime"}', $annotation['value']);
        $this->assertSame(1505855799433901, $annotation['timestamp']);

        $this->assertSame('AuthService', $row['remoteEndpoint']['serviceName']);
    }

    public function test_should_omit_empty_keys_from_zipkin_span(): void
    {
        $span = (new SpanData());

        $converter = new SpanConverter('unused');
        $row = $converter->convert([$span])[0];

        $this->assertArrayNotHasKey('kind', $row);
        $this->assertArrayNotHasKey('parentId', $row);
        $this->assertArrayNotHasKey('tags', $row);
        $this->assertArrayNotHasKey('otel.library.name', $row);
        $this->assertArrayNotHasKey('otel.library.version', $row);
    }

    /**
     * @dataProvider spanKindProvider
     */
    public function test_should_convert_otel_span_to_a_zipkin_span(int $internalSpanKind, string $expectedSpanKind): void
    {
        $span = (new SpanData())
            ->setKind($internalSpanKind);

        $converter = new SpanConverter('unused');
        $row = $converter->convert([$span])[0];

        $this->assertSame($expectedSpanKind, $row['kind']);
    }

    public function spanKindProvider(): array
    {
        return [
            'server' => [SpanKind::KIND_SERVER, ZipkinSpanKind::SERVER],
            'client' => [SpanKind::KIND_CLIENT, ZipkinSpanKind::CLIENT],
            'producer' => [SpanKind::KIND_PRODUCER, ZipkinSpanKind::PRODUCER],
            'consumer' => [SpanKind::KIND_CONSUMER, ZipkinSpanKind::CONSUMER],
        ];
    }

    /**
     * @dataProvider unmappedSpanKindProvider
     */
    public function test_should_convert_an_unmapped_otel_internal_span_to_a_zipkin_span_of_unspecified_kind($kind): void
    {
        $span = (new SpanData())
            ->setKind($kind);

        $converter = new SpanConverter('unused');
        $row = $converter->convert([$span])[0];

        $this->assertArrayNotHasKey('kind', $row);
    }

    public function unmappedSpanKindProvider(): array
    {
        return [
            'internal' => [SpanKind::KIND_INTERNAL],
            'undefined' => [12345], //Some number not in the SpanKind "enum"
        ];
    }

    public function test_should_convert_an_event_without_attributes_to_an_annotation_with_only_its_name(): void
    {
        $span = (new SpanData())
            ->addEvent('event.name', new Attributes());

        $converter = new SpanConverter('test.name');
        $row = $converter->convert([$span])[0];

        [$annotation] = $row['annotations'];
        $this->assertSame('"event.name"', $annotation['value']);
    }

    public function test_should_use_otel_ipv_4_and_port_correctly_for_zipkin_remote_endpoint(): void
    {
        $span = (new SpanData())
            ->addAttribute('net.peer.ip', '255.255.255.255')
            ->addAttribute('net.peer.port', '80')
            ->setKind(SpanKind::KIND_PRODUCER);

        $converter = new SpanConverter('unused');
        $row = $converter->convert([$span])[0];

        $this->assertSame('unknown', $row['remoteEndpoint']['serviceName']);
        $this->assertSame(pow(2, 32)-1, $row['remoteEndpoint']['ipv4']);
        $this->assertSame(80, $row['remoteEndpoint']['port']);
    }

    public function test_should_use_otel_ipv_6_correctly_for_zipkin_remote_endpoint(): void
    {
        $span = (new SpanData())
            ->addAttribute('net.peer.ip', '::1')
            ->setKind(SpanKind::KIND_PRODUCER);

        $converter = new SpanConverter('unused');
        $row = $converter->convert([$span])[0];

        $this->assertSame('00000000000000000000000000000001', bin2hex($row['remoteEndpoint']['ipv6'])); //Couldn't figure out how to do a direct assertion against binary data
    }

    public function test_tags_are_coerced_correctly_to_strings(): void
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

        $tags = (new SpanConverter('tags.test'))->convert([$span])[0]['tags'];

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
