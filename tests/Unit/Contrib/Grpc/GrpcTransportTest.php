<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Grpc;

use Exception;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @covers \OpenTelemetry\Contrib\Grpc\GrpcTransportFactory
 * @covers \OpenTelemetry\Contrib\Grpc\GrpcTransport
 */
final class GrpcTransportTest extends TestCase
{
    public function test_grpc_transport_supports_only_protobuf(): void
    {
        $factory = new GrpcTransportFactory();
        $transport = $factory->create('http://localhost/service/method');

        $response = $transport->send('', 'text/plain');

        $this->expectException(UnexpectedValueException::class);
        $response->await();
    }

    public function test_invalid_compression(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $factory = new GrpcTransportFactory();
        $factory->create('http://localhost/service/method', [], 'invalid');
    }

    public function test_shutdown_returns_true(): void
    {
        $factory = new GrpcTransportFactory();
        $transport = $factory->create('http://localhost/service/method');

        $this->assertTrue($transport->shutdown());
    }

    public function test_force_flush_returns_true(): void
    {
        $factory = new GrpcTransportFactory();
        $transport = $factory->create('http://localhost/service/method');

        $this->assertTrue($transport->forceFlush());
    }

    public function test_send_closed_returns_error(): void
    {
        $factory = new GrpcTransportFactory();
        $transport = $factory->create('http://localhost/service/method');
        $transport->shutdown();

        $response = $transport->send('', 'text/plain');

        $this->expectException(Exception::class);
        $response->await();
    }

    public function test_shutdown_closed_returns_false(): void
    {
        $factory = new GrpcTransportFactory();
        $transport = $factory->create('http://localhost/service/method');
        $transport->shutdown();

        $this->assertFalse($transport->shutdown());
    }

    public function test_force_flush_closed_returns_false(): void
    {
        $factory = new GrpcTransportFactory();
        $transport = $factory->create('http://localhost/service/method');
        $transport->shutdown();

        $this->assertFalse($transport->forceFlush());
    }
}
