<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use PHPUnit\Framework\TestCase;

class ConsoleSpanExporterTest extends TestCase
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
        ],],
    ];

    public function testExportSuccess(): void
    {
        $converter = $this->createMock(SpanConverterInterface::class);
        $converter->expects($this->once())
            ->method('convert')
            ->willReturn(self::TEST_DATA);

        ob_start();

        $this->assertSame(
            SpanExporterInterface::STATUS_SUCCESS,
            (new ConsoleSpanExporter($converter))->export([
                $this->createMock(SpanDataInterface::class),
            ])
        );

        ob_end_clean();
    }

    public function testExportFailed(): void
    {
        $resource = fopen('php://stdin', 'rb');
        $converter = $this->createMock(SpanConverterInterface::class);
        $converter->expects($this->once())
            ->method('convert')
            ->willReturn([$resource]);

        ob_start();

        $this->assertSame(
            SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE,
            (new ConsoleSpanExporter($converter))->export([
                $this->createMock(SpanDataInterface::class),
            ])
        );

        ob_end_clean();
        fclose($resource);
    }

    public function testExportOutput(): void
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
        ]);
    }

    public function testFromConnectionString(): void
    {
        $this->assertInstanceOf(
            ConsoleSpanExporter::class,
            ConsoleSpanExporter::fromConnectionString()
        );
    }

    public function testShutdown(): void
    {
        $this->assertTrue((new ConsoleSpanExporter())->shutdown());
    }

    public function testForceFlush(): void
    {
        $this->assertTrue((new ConsoleSpanExporter())->forceFlush());
    }
}
