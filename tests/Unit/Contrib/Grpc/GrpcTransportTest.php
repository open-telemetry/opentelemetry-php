<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Grpc;

use Exception;
use InvalidArgumentException;
use OpenTelemetry\Contrib\Grpc\GrpcTransport;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GrpcTransport::class)]
final class GrpcTransportTest extends TestCase
{
    private GrpcTransport $transport;

    public function setUp(): void
    {
        $this->transport = new GrpcTransport('http://localhost:4317', [], '/method', [], 123);
    }

    public function test_grpc_transport_supports_only_protobuf(): void
    {
        $factory = new GrpcTransportFactory();

        $this->expectException(InvalidArgumentException::class);
        /** @psalm-suppress InvalidArgument @phpstan-ignore-next-line */
        $factory->create('http://localhost/service/method', 'text/plain');
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

        $response = $this->transport->send('');

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
