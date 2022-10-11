<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use OpenTelemetry\Contrib\Otlp\Exporter;
use OpenTelemetry\Contrib\Otlp\Protocols;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Contrib\Otlp\Exporter
 */
class ExporterTest extends TestCase
{
    private MockObject $transport;
    private Exporter $exporter;

    public function setUp(): void
    {
        $this->transport = $this->createMock(TransportInterface::class);
        $this->exporter = new Exporter($this->transport, Protocols::HTTP_PROTOBUF);
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
}
