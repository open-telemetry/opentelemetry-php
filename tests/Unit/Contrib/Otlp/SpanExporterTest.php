<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use function fseek;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function stream_get_contents;

/**
 * @covers \OpenTelemetry\Contrib\Otlp\SpanExporter
 */
class SpanExporterTest extends TestCase
{
    private MockObject $transport;
    private SpanExporter $exporter;

    public function setUp(): void
    {
        $this->transport = $this->createMock(TransportInterface::class);
        $this->transport->method('contentType')->willReturn('application/x-protobuf');
        $this->exporter = new SpanExporter($this->transport);
    }

    public function test_export_with_transport_failure(): void
    {
        $future = new ErrorFuture(new \Exception('foo'));
        $this->transport->method('send')->willReturn($future);
        $result = $this->exporter->export([]);
        $this->assertFalse($result->await());
    }

    public function test_export_with_invalid_response(): void
    {
        $future = new CompletedFuture('invalid.grpc.payload');
        $this->transport->method('send')->willReturn($future);
        $result = $this->exporter->export([]);
        $this->assertFalse($result->await());
    }

    public function test_export_success(): void
    {
        $future = new CompletedFuture('');
        $this->transport->method('send')->willReturn($future);
        $result = $this->exporter->export([]);
        $this->assertTrue($result->await());
    }

    public function test_shutdown(): void
    {
        $this->transport->expects($this->once())->method('shutdown');
        $this->exporter->shutdown();
    }

    public function test_force_flush(): void
    {
        $this->transport->expects($this->once())->method('forceFlush');
        $this->exporter->forceFlush();
    }

    public function test_json_span_and_trace_id_hex_format(): void
    {
        $stream = fopen('php://memory', 'a+b');
        $transport = new StreamTransport($stream, 'application/json');
        $exporter = new SpanExporter($transport);

        $exporter->export([
            (new SpanData())->setContext(SpanContext::create('0af7651916cd43dd8448eb211c80319c', 'b7ad6b7169203331')),
        ]);

        fseek($stream, 0);
        $this->assertJsonStringEqualsJsonString(<<<TRACE
            {
                "resourceSpans": [
                    {
                        "resource": {},
                        "scopeSpans": [
                            {
                                "scope": {},
                                "spans": [
                                    {
                                        "traceId": "0af7651916cd43dd8448eb211c80319c",
                                        "spanId": "b7ad6b7169203331",
                                        "name": "test-span-data",
                                        "kind": 1,
                                        "startTimeUnixNano": "1505855794194009601",
                                        "endTimeUnixNano": "1505855799465726528",
                                        "status": {}
                                    }
                                ]
                            }
                        ]
                    }
                ]
            }
            TRACE, stream_get_contents($stream));
    }
}
