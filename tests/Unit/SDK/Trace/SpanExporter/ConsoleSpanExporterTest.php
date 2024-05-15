<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @psalm-suppress UndefinedInterfaceMethod
 */
#[CoversClass(ConsoleSpanExporter::class)]
class ConsoleSpanExporterTest extends AbstractExporterTestCase
{
    public function createExporter(): ConsoleSpanExporter
    {
        return new ConsoleSpanExporter($this->transport);
    }

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
        ],],
    ];

    public function test_export_output(): void
    {
        try {
            $expected = json_encode(self::TEST_DATA, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT) . PHP_EOL;
        } catch (\Throwable $t) {
            $this->fail($t->getMessage());
        }
        $this->future->allows([
            'await' => true,
        ]);

        $converter = $this->createMock(SpanConverterInterface::class);
        $converter->expects($this->once())
            ->method('convert')
            ->willReturn(self::TEST_DATA);

        (new ConsoleSpanExporter($this->transport, $converter))->export([
            $this->createMock(SpanDataInterface::class),
        ])->await();

        $this->transport->shouldHaveReceived('send')->with($expected);
    }

    public function createExporterWithTransport(TransportInterface $transport): SpanExporterInterface
    {
        return new ConsoleSpanExporter($transport);
    }

    public function getExporterClass(): string
    {
        return ConsoleSpanExporter::class;
    }
}
