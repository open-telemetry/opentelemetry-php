<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Zipkin;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Contrib\Zipkin\SpanConverter;
use OpenTelemetry\Contrib\Zipkin\SpanKind as ZipkinSpanKind;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\Contrib\Zipkin\SpanConverter
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
            ->setInstrumentationScope(new InstrumentationScope(
                'instrumentation_scope_name',
                'instrumentation_scope_version',
                null,
                Attributes::create([]),
            ))
            ->addAttribute('service', 'guard')
            ->addAttribute('net.peer.name', 'authorizationservice.com')
            ->addAttribute('peer.service', 'AuthService')
            ->setResource(
                ResourceInfo::create(
                    Attributes::create([
                        'telemetry.sdk.name' => 'opentelemetry',
                        'telemetry.sdk.language' => 'php',
                        'telemetry.sdk.version' => 'dev',
                        'instance' => 'test-a',
                        'service.name' => 'unknown_service:php',
                    ])
                )
            )
            ->addEvent('validators.list', Attributes::create(['job' => 'stage.updateTime']), 1505855799433901068)
            ->setHasEnded(true);

        $converter = new SpanConverter();
        $row = $converter->convert([$span])[0];

        $this->assertSame($span->getContext()->getSpanId(), $row['id']);
        $this->assertSame($span->getContext()->getTraceId(), $row['traceId']);
        $this->assertSame('1000000000000000', $row['parentId']);

        $this->assertSame('unknown_service:php', $row['localEndpoint']['serviceName']);
        $this->assertSame($span->getName(), $row['name']);

        $this->assertSame(1505855794194009, $row['timestamp']);
        $this->assertSame(5271717, $row['duration']);

        $this->assertCount(10, $row['tags']);

        $this->assertSame('Error', $row['tags']['otel.status_code']);
        $this->assertSame('status_description', $row['tags']['error']);
        $this->assertSame('guard', $row['tags']['service']);
        $this->assertSame('authorizationservice.com', $row['tags']['net.peer.name']);
        $this->assertSame('AuthService', $row['tags']['peer.service']);
        $this->assertSame('opentelemetry', $row['tags']['telemetry.sdk.name']);
        $this->assertSame('php', $row['tags']['telemetry.sdk.language']);
        $this->assertSame('dev', $row['tags']['telemetry.sdk.version']);
        $this->assertSame('test-a', $row['tags']['instance']);
        $this->assertSame('unknown_service:php', $row['tags']['service.name']);

        $this->assertSame('instrumentation_scope_name', $row['otel.scope.name']);
        $this->assertSame('instrumentation_scope_version', $row['otel.scope.version']);

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

        $converter = new SpanConverter();
        $row = $converter->convert([$span])[0];

        $this->assertArrayNotHasKey('kind', $row);
        $this->assertArrayNotHasKey('parentId', $row);
        $this->assertArrayNotHasKey('tags', $row);
        $this->assertArrayNotHasKey('otel.scope.name', $row);
        $this->assertArrayNotHasKey('otel.scope.version', $row);
    }

    /**
     * @dataProvider spanKindProvider
     */
    public function test_should_convert_otel_span_to_a_zipkin_span(int $internalSpanKind, string $expectedSpanKind): void
    {
        $span = (new SpanData())
            ->setKind($internalSpanKind);

        $converter = new SpanConverter();
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

        $converter = new SpanConverter();
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
            ->addEvent('event.name', Attributes::create([]));

        $converter = new SpanConverter();
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

        $converter = new SpanConverter();
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

        $converter = new SpanConverter();
        $row = $converter->convert([$span])[0];

        $this->assertSame('00000000000000000000000000000001', bin2hex($row['remoteEndpoint']['ipv6'])); //Couldn't figure out how to do a direct assertion against binary data
    }

    public function test_tags_are_coerced_correctly_to_strings(): void
    {
        $listOfStrings = ['string-1', 'string-2'];
        $listOfNumbers = [1, 2, 3, 3.1415, 42];
        $listOfBooleans = [true, true, false, true];

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
            ->addAttribute('list-of-booleans', $listOfBooleans);

        $tags = (new SpanConverter())->convert([$span])[0]['tags'];

        // Check that we can convert all attributes to tags
        $this->assertCount(9, $tags);

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

        // Lists must be cast to strings and joined with a separator
        $this->assertSame(implode(',', $listOfStrings), $tags['list-of-strings']);
        $this->assertSame(implode(',', $listOfNumbers), $tags['list-of-numbers']);
        $this->assertSame('true,true,false,true', $tags['list-of-booleans']);
    }

    /**
     * @dataProvider droppedProvider
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.20.0/specification/common/mapping-to-non-otlp.md#dropped-attributes-count
     */
    public function test_displays_non_zero_dropped_counts(int $dropped, bool $expected): void
    {
        $attributes = $this->createMock(AttributesInterface::class);
        $attributes->method('getDroppedAttributesCount')->willReturn($dropped);
        $spanData = $this->createMock(SpanDataInterface::class);
        $spanData->method('getAttributes')->willReturn($attributes);
        $spanData->method('getLinks')->willReturn([]);
        $spanData->method('getEvents')->willReturn([]);
        $spanData->method('getTotalDroppedEvents')->willReturn($dropped);
        $spanData->method('getTotalDroppedLinks')->willReturn($dropped);

        $converter = new SpanConverter();
        $converted = $converter->convert([$spanData])[0];
        $tags = $converted['tags'];

        if ($expected) {
            $this->assertArrayHasKey(SpanConverter::KEY_DROPPED_EVENTS_COUNT, $tags);
            $this->assertArrayHasKey(SpanConverter::KEY_DROPPED_LINKS_COUNT, $tags);
            $this->assertArrayHasKey(SpanConverter::KEY_DROPPED_ATTRIBUTES_COUNT, $tags);
        } else {
            $this->assertArrayNotHasKey(SpanConverter::KEY_DROPPED_EVENTS_COUNT, $tags);
            $this->assertArrayNotHasKey(SpanConverter::KEY_DROPPED_LINKS_COUNT, $tags);
            $this->assertArrayNotHasKey(SpanConverter::KEY_DROPPED_ATTRIBUTES_COUNT, $tags);
        }
    }

    public static function droppedProvider(): array
    {
        return [
            'no dropped' => [0, false],
            'some dropped' => [1, true],
        ];
    }
}
