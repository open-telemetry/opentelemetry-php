<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\SpanExporter;

use ArrayIterator;
use OpenTelemetry\API\AttributeInterface;
use OpenTelemetry\API\AttributesInterface;
use OpenTelemetry\API\AttributesIteratorInterface;
use OpenTelemetry\API\Trace\EventInterface;
use OpenTelemetry\API\Trace\LinkInterface;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\SDK\Attribute;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\FriendlySpanConverter;
use OpenTelemetry\SDK\Trace\StatusDataInterface;
use PHPUnit\Framework\TestCase;

//use DG\BypassFinals;

class FriendlySpanConverterTest extends TestCase
{
    private const TEST_DATA = [
        'name' => 'my.service',
        'parent_span_id' => '1000000000000000',
        'kind' => 'KIND_INTERNAL',
        'start' => 1505855794194009601,
        'end' => 1505855799465726528,
        'context' => [
            'trace_id' => '00000000000000000000000000000000',
            'span_id' => '0000000000000000',
            'trace_state' => 'foz=baz,foo=bar',
        ],
        'resource' => [
            'telemetry.sdk.name' => 'opentelemetry',
            'telemetry.sdk.language' => 'php',
            'telemetry.sdk.version' => 'dev',
        ],
        'attributes' => [
            'fruit' => 'apple',
        ],
        'status' => [
            'code' => 'Error',
            'description' => 'status_description',
        ],
        'events' => [[
            'name' => 'validators.list',
            'timestamp' => 1505855799433901068,
            'attributes' => [
                'job' => 'stage.updateTime',
            ],
        ],[
            'name' => 'user.created',
            'timestamp' => 1505855799433901555,
            'attributes' => [
                'name' => 'John Doe',
            ],
        ],],
        'links' => [[
            'context' => [
                'trace_id' => '20000000000000000000000000000000',
                'span_id' => '2000000000000000',
                'trace_state' => 'foo=baz,foz=bar',
            ],
            'attributes' => [
                'foo' => 'bar',
            ], ],[
            'context' => [
                'trace_id' => '20000000000000000000000000000000',
                'span_id' => '3000000000000000',
                'trace_state' => 'baz=foz,bar=foo',
            ],
            'attributes' => [
                'foz' => 'baz',
            ], ],
        ],
    ];

    public function testConvert(): void
    {
        $this->assertEquals(
            self::TEST_DATA,
            (new FriendlySpanConverter())->convert(
                [$this->createSpanDataInterfaceMock()]
            )[0]
        );
    }

    private function createSpanDataInterfaceMock(): SpanDataInterface
    {
        $mock = $this->getMockBuilder(SpanDataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getName')
            ->willReturn(self::TEST_DATA['name']);

        $mock->method('getContext')
            ->willReturn(
                $this->createSpanContextMock(
                    self::TEST_DATA['context']['span_id'],
                    self::TEST_DATA['context']['trace_id'],
                    self::TEST_DATA['context']['trace_state'],
                )
            );

        $mock->method('getParentContext')
            ->willReturn(
                $this->createSpanContextMock(
                    self::TEST_DATA['parent_span_id'],
                )
            );

        $mock->method('getResource')
            ->willReturn(
                $this->createResourceInfoMock()
            );

        $mock->method('getKind')
            ->willReturn(SpanKind::KIND_INTERNAL);

        $mock->method('getStartEpochNanos')
            ->willReturn(self::TEST_DATA['start']);

        $mock->method('getEndEpochNanos')
            ->willReturn(self::TEST_DATA['end']);

        $mock->method('getAttributes')
            ->willReturn($this->createAttributesInterfaceMock(self::TEST_DATA['attributes']));

        $mock->method('getStatus')
            ->willReturn($this->createStatusDataMock());

        $events = [];
        foreach (self::TEST_DATA['events'] as $event) {
            $events[] = $this->createEventInterfaceMock(
                $event['name'],
                $event['timestamp'],
                $this->createAttributesInterfaceMock(
                    $event['attributes']
                )
            );
        }
        $mock->method('getEvents')->willReturn($events);

        $links = [];
        foreach (self::TEST_DATA['links'] as $link) {
            $links[] = $this->createLinkInterfaceMock(
                $this->createSpanContextMock(
                    $link['context']['span_id'],
                    $link['context']['trace_id'],
                    $link['context']['trace_state']
                ),
                $this->createAttributesInterfaceMock($link['attributes'])
            );
        }
        $mock->method('getLinks')->willReturn($links);

        return $mock;
    }

    private function createSpanContextMock(string $spanId, string $traceId = '0', string $traceState = null): SpanContextInterface
    {
        $mock = $this->createMock(SpanContextInterface::class);

        $mock->method('getSpanId')
            ->willReturn($spanId);

        $mock->method('getTraceId')
            ->willReturn($traceId);

        $mock->method('getTraceState')
            ->willReturn(
                $traceState === null ? null : $this->createTraceStateInterfaceMock($traceState)
            );

        $mock->method('isValid')
            ->willReturn(true);

        return $mock;
    }

    private function createTraceStateInterfaceMock(string $traceState): TraceStateInterface
    {
        $mock = $this->createMock(TraceStateInterface::class);

        $mock->method('__toString')
            ->willReturn($traceState);

        return $mock;
    }

    private function createResourceInfoMock(): ResourceInfo
    {
        $mock = $this->getMockBuilder(ResourceInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getAttributes')
            ->willReturn($this->createAttributesInterfaceMock(self::TEST_DATA['resource']));

        return $mock;
    }

    private function createStatusDataMock(): StatusDataInterface
    {
        $mock = $this->createMock(StatusDataInterface::class);

        $mock->method('getCode')
            ->willReturn(self::TEST_DATA['status']['code']);

        $mock->method('getDescription')
            ->willReturn(self::TEST_DATA['status']['description']);

        return $mock;
    }

    private function createAttributesInterfaceMock(array $items): AttributesInterface
    {
        $mock = $this->createMock(AttributesInterface::class);

        $attributes = [];
        foreach ($items as $key => $value) {
            $attributes[$key] = $this->createAttributeMock($key, $value);
        }
        $mock->method('getIterator')
        ->willReturn(
            $this->createAttributesIterator($attributes)
        );

        return $mock;
    }

    private function createAttributeMock(string $key, $value): Attribute
    {
        $mock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getKey')
            ->willReturn($key);

        $mock->method('getValue')
            ->willReturn($value);

        return $mock;
    }

    public function createEventInterfaceMock(string $name, int $timestamp, AttributesInterface $attributes): EventInterface
    {
        $mock = $this->createMock(EventInterface::class);

        $mock->method('getName')->willReturn($name);
        $mock->method('getEpochNanos')->willReturn($timestamp);
        $mock->method('getAttributes')->willReturn($attributes);

        return $mock;
    }

    public function createLinkInterfaceMock(SpanContextInterface $context, AttributesInterface $attributes): LinkInterface
    {
        $mock = $this->createMock(LinkInterface::class);

        $mock->method('getSpanContext')->willReturn($context);
        $mock->method('getAttributes')->willReturn($attributes);

        return $mock;
    }

    public function createAttributesIterator(array $items): AttributesIteratorInterface
    {
        return new class($items) implements AttributesIteratorInterface {
            private ArrayIterator $iterator;
            public function __construct($attributes)
            {
                $this->iterator = new ArrayIterator($attributes);
            }

            public function key(): string
            {
                return (string) $this->iterator->key();
            }

            public function current(): AttributeInterface
            {
                return $this->iterator->current();
            }

            public function rewind(): void
            {
                $this->iterator->rewind();
            }

            public function valid(): bool
            {
                return $this->iterator->valid();
            }

            public function next(): void
            {
                $this->iterator->next();
            }
        };
    }
}
