<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Instana;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Contrib\Instana\SpanConverter as InstanaSpanConverter;
use OpenTelemetry\Contrib\Instana\SpanKind as InstanaSpanKind;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InstanaSpanConverter::class)]
class InstanaSpanConverterTest extends TestCase
{
    public function test_should_convert_a_span_to_a_payload_for_instana(): void
    {
        $span = (new SpanData())
            ->setName('converter.test')
            ->setKind(SpanKind::KIND_CLIENT)
            ->setContext(
                SpanContext::create(
                    'abcdef0123456789abcdef0123456789',
                    'aabbccddeeff0123'
                )
            )
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
            ->addAttribute('service', array('name' => 'unknown_service:php', 'version' => 'dev-main'))
            ->addAttribute('net.peer.name', 'authorizationservice.com')
            ->addAttribute('peer.service', 'AuthService')
            ->setResource(
                ResourceInfo::create(
                    Attributes::create([
                        'telemetry.sdk.name' => 'opentelemetry',
                        'telemetry.sdk.language' => 'php',
                        'telemetry.sdk.version' => 'dev',
                        'instance' => 'test-a'
                    ])
                )
            )
            ->addEvent('validators.list', Attributes::create(['job' => 'stage.updateTime']), 1505855799433901068)
            ->setHasEnded(true);

        $converter = new InstanaSpanConverter('0123456abcdef', '12345');
        $instanaSpan = $converter->convert([$span])[0];

        $this->assertSame($span->getContext()->getSpanId(), $instanaSpan['s']);
        $this->assertSame($span->getContext()->getTraceId(), $instanaSpan['t']);
        $this->assertSame('1000000000000000', $instanaSpan['p']);

        $this->assertSame('unknown_service:php', $instanaSpan['data']['otel']['service']['name']);
        $this->assertSame($span->getName(), $instanaSpan['n']);

        $this->assertSame(1505855794194, $instanaSpan['ts']);
        $this->assertSame(5271, $instanaSpan['d']);

        $this->assertCount(7, $instanaSpan['data']);

        $this->assertSame('Error', $instanaSpan['data']['otel']['status_code']);
        $this->assertSame('status_description', $instanaSpan['data']['otel']['error']);
        $this->assertSame('authorizationservice.com', $instanaSpan['data']['net']['peer.name']);
        $this->assertSame('AuthService', $instanaSpan['data']['peer']['service']);
        $this->assertSame('opentelemetry', $instanaSpan['data']['telemetry']['sdk.name']);
        $this->assertSame('php', $instanaSpan['data']['telemetry']['sdk.language']);
        $this->assertSame('dev', $instanaSpan['data']['telemetry']['sdk.version']);
        $this->assertSame('test-a', $instanaSpan['data']['instance']);
        $this->assertSame('unknown_service:php', $instanaSpan['data']['otel']['service']['name']);
        $this->assertSame('dev-main', $instanaSpan['data']['otel']['service']['version']);
        
        $this->assertSame('instrumentation_scope_name', $instanaSpan['data']['otel']['scope.name']);
        $this->assertSame('instrumentation_scope_version', $instanaSpan['data']['otel']['scope.version']);
        $this->assertSame('converter.test', $instanaSpan['data']['service']);

        $this->assertSame('12345', $instanaSpan['f']['e']);
        $this->assertSame('0123456abcdef', $instanaSpan['f']['h']);

        $this->assertSame(2, $instanaSpan['k']);
    }

    #[DataProvider('spanConverterProvider')]
    public function test_should_throw_on_missing_construction(InstanaSpanConverter $converter): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to get agentUuid or agentPid');
        $span = (new SpanData());
        $converter->convert([$span]);
    }

    public static function spanConverterProvider(): array
    {
        return [
            'default' => [new InstanaSpanConverter()],
            'wo_uuid' => [new InstanaSpanConverter(agentPid: '12345')],
            'wo_pid' => [new InstanaSpanConverter(agentUuid: '0123456abcdef')]
        ];
    }

    public function test_should_omit_empty_keys_from_instana_span(): void
    {
        $span = (new SpanData());

        $converter = new InstanaSpanConverter('0123456abcdef', '12345');
        $instanaSpan = $converter->convert([$span])[0];

        $this->assertArrayNotHasKey('p', $instanaSpan);
        $this->assertArrayNotHasKey('otel', $instanaSpan['data']);
        $this->assertCount(1, $instanaSpan['data']);
    }

    #[DataProvider('spanKindProvider')]
    public function test_should_convert_otel_span_to_an_instana_span(int $internalSpanKind, ?int $expectedSpanKind): void
    {
        $span = (new SpanData())
            ->setKind($internalSpanKind);

        $converter = new InstanaSpanConverter('0123456abcdef', '12345');
        $instanaSpan = $converter->convert([$span])[0];

        $this->assertSame($expectedSpanKind, $instanaSpan['k']);
    }

    public static function spanKindProvider(): array
    {
        return [
            'server' => [SpanKind::KIND_SERVER, InstanaSpanKind::ENTRY],
            'client' => [SpanKind::KIND_CLIENT, InstanaSpanKind::EXIT],
            'producer' => [SpanKind::KIND_PRODUCER, InstanaSpanKind::EXIT],
            'consumer' => [SpanKind::KIND_CONSUMER, InstanaSpanKind::ENTRY],
            'consumer' => [SpanKind::KIND_INTERNAL, InstanaSpanKind::INTERMEDIATE],
            'default' => [12345, null] // Some unsupported "enum"
        ];
    }

    public function test_should_convert_an_event_without_attributes_to_an_empty_event(): void
    {
        $span = (new SpanData())
            ->addEvent('event.name', Attributes::create([]));

        $converter = new InstanaSpanConverter('0123456abcdef', '12345');
        $instanaSpan = $converter->convert([$span])[0];

        $this->assertSame('{}', $instanaSpan['data']['events']['event.name']);
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod,PossiblyInvalidArrayAccess
     */
    public function test_data_are_coerced_correctly_to_strings(): void
    {
        $listOfStrings = ['string-1', 'string-2'];
        $listOfNumbers = [1, 2, 3, 3.1415, 42];
        $listOfBooleans = [true, true, false, true];

        $span = (new SpanData())
            ->addAttribute('string', 'string')
            ->addAttribute('integer-1', 1024)
            ->addAttribute('integer-2', 0)
            ->addAttribute('float', 1.2345)
            ->addAttribute('boolean-1', true)
            ->addAttribute('boolean-2', false)
            ->addAttribute('list-of-strings', $listOfStrings)
            ->addAttribute('list-of-numbers', $listOfNumbers)
            ->addAttribute('list-of-booleans', $listOfBooleans);

        $data = (new InstanaSpanConverter('0123456abcdef', '12345'))->convert([$span])[0]['data'];
        
        // Check that we captured all attributes in data.
        $this->assertCount(10, $data);

        $this->assertSame('string', $data['string']);
        $this->assertSame(1024, $data['integer-1']);
        $this->assertSame(0, $data['integer-2']);
        $this->assertSame(1.2345, $data['float']);
        $this->assertSame(true, $data['boolean-1']);
        $this->assertSame(false, $data['boolean-2']);

        // Lists are recovered and are the same.
        $this->assertSame($listOfStrings, $data['list-of-strings']);
        $this->assertSame($listOfNumbers, $data['list-of-numbers']);
        $this->assertSame($listOfBooleans, $data['list-of-booleans']);
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.20.0/specification/common/mapping-to-non-otlp.md#dropped-attributes-count
     */
    #[DataProvider('droppedProvider')]
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

        $converter = new InstanaSpanConverter('0123456abcdef', '12345');
        $converted = $converter->convert([$spanData])[0];
        $data = $converted['data']['otel'];

        if ($expected) {
            $this->assertArrayHasKey(InstanaSpanConverter::OTEL_KEY_DROPPED_EVENTS_COUNT, $data);
            $this->assertSame($dropped, $data[InstanaSpanConverter::OTEL_KEY_DROPPED_EVENTS_COUNT]);
            $this->assertArrayHasKey(InstanaSpanConverter::OTEL_KEY_DROPPED_LINKS_COUNT, $data);
            $this->assertSame($dropped, $data[InstanaSpanConverter::OTEL_KEY_DROPPED_LINKS_COUNT]);
            $this->assertArrayHasKey(InstanaSpanConverter::OTEL_KEY_DROPPED_ATTRIBUTES_COUNT, $data);
            $this->assertSame($dropped, $data[InstanaSpanConverter::OTEL_KEY_DROPPED_ATTRIBUTES_COUNT]);
        } else {
            $this->assertArrayNotHasKey(InstanaSpanConverter::OTEL_KEY_DROPPED_EVENTS_COUNT, $data);
            $this->assertArrayNotHasKey(InstanaSpanConverter::OTEL_KEY_DROPPED_LINKS_COUNT, $data);
            $this->assertArrayNotHasKey(InstanaSpanConverter::OTEL_KEY_DROPPED_ATTRIBUTES_COUNT, $data);
        }
    }

    public static function droppedProvider(): array
    {
        return [
            'no dropped' => [0, false],
            'some dropped' => [1, true],
        ];
    }

    public function test_events(): void
    {
        $eventAttributes = $this->createMock(AttributesInterface::class);
        $eventAttributes->method('getDroppedAttributesCount')->willReturn(99);
        $attributes = [
            'a_one' => 123,
            'a_two' => 3.14159,
            'a_three' => true,
            'a_four' => false,
        ];
        $eventAttributes->method('count')->willReturn(count($attributes));
        $eventAttributes->method('toArray')->willReturn($attributes);
        $span = (new SpanData())
            ->setName('events.test')
            ->addEvent('event.one', $eventAttributes);
        $instanaSpan = (new InstanaSpanConverter('0123456abcdef', '12345'))->convert([$span])[0];

        $events = $instanaSpan['data']['events'];

        $this->assertTrue(array_key_exists('event.one', $events));
        $this->assertIsString($events['event.one']);
    }
}
