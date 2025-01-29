<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Grpc;

use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GrpcTransportFactory::class)]
class GrpcTransportFactoryTest extends TestCase
{
    public function test_grpc_transport_create(): void
    {
        $factory = new GrpcTransportFactory();
        $transport = $factory->create('http://localhost/service/method');

        $this->assertInstanceOf(TransportInterface::class, $transport);
    }

    public function test_type(): void
    {
        $factory = new GrpcTransportFactory();
        $this->assertSame('grpc', $factory->type());
    }

    public function test_priority(): void
    {
        $factory = new GrpcTransportFactory();
        $this->assertSame(0, $factory->priority());
    }
}
