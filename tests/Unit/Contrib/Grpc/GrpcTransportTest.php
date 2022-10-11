<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Grpc;

use Exception;
use OpenTelemetry\Contrib\Grpc\GrpcTransport;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @covers \OpenTelemetry\Contrib\Grpc\GrpcTransport
 * Note that real ExportTraceService* objects are used, since mocking them segfaults with ext-protobuf
 */
final class GrpcTransportTest extends TestCase
{
    private GrpcTransport $transport;

    public function setUp(): void
    {
        $this->transport = new GrpcTransport('http://localhost:4317', [], '/method', []);
    }

    public function test_grpc_transport_supports_only_protobuf(): void
    {
        $response = $this->transport->send('', 'text/plain');

        $this->expectException(UnexpectedValueException::class);
        $response->await();
    }

    public function test_shutdown_returns_true(): void
    {
        $this->assertTrue($this->transport->shutdown());
    }

    public function test_force_flush_returns_true(): void
    {
        $this->assertTrue($this->transport->forceFlush());
    }

    public function test_send_closed_returns_error(): void
    {
        $this->transport->shutdown();

        $response = $this->transport->send('', TransportInterface::CONTENT_TYPE_PROTOBUF);

        $this->expectException(Exception::class);
        $response->await();
    }

    public function test_shutdown_closed_returns_false(): void
    {
        $this->transport->shutdown();

        $this->assertFalse($this->transport->shutdown());
    }

    public function test_force_flush_closed_returns_false(): void
    {
        $this->transport->shutdown();

        $this->assertFalse($this->transport->forceFlush());
    }
}
