<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;

/**
 * @covers \OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter
 */
class ConsoleSpanExporterTest extends AbstractExporterTest
{
    public function createExporter(): ConsoleSpanExporter
    {
        return new ConsoleSpanExporter();
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

    public function test_export_success(): void
    {
        $converter = $this->createMock(SpanConverterInterface::class);
        $converter->expects($this->once())
            ->method('convert')
            ->willReturn(self::TEST_DATA);

        ob_start();

        $this->assertTrue(
            (new ConsoleSpanExporter($converter))->export([
                $this->createMock(SpanDataInterface::class),
            ])->await(),
        );

        ob_end_clean();
    }

    public function test_export_failed(): void
    {
        $resource = fopen('php://stdin', 'rb');
        $converter = $this->createMock(SpanConverterInterface::class);
        $converter->expects($this->once())
            ->method('convert')
            ->willReturn([$resource]);

        ob_start();

        $this->assertFalse(
            (new ConsoleSpanExporter($converter))->export([
                $this->createMock(SpanDataInterface::class),
            ])->await(),
        );

        ob_end_clean();
        fclose($resource);
    }

    public function test_export_output(): void
    {
        try {
            $expected = json_encode(self::TEST_DATA, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT) . PHP_EOL;
        } catch (\Throwable $t) {
            $this->fail($t->getMessage());
        }

        $converter = $this->createMock(SpanConverterInterface::class);
        $converter->expects($this->once())
            ->method('convert')
            ->willReturn(self::TEST_DATA);

        $this->expectOutputString($expected);

        (new ConsoleSpanExporter($converter))->export([
            $this->createMock(SpanDataInterface::class),
        ])->await();
    }

    public function test_from_connection_string(): void
    {
        $this->assertInstanceOf(
            ConsoleSpanExporter::class,
            ConsoleSpanExporter::fromConnectionString()
        );
    }
}
