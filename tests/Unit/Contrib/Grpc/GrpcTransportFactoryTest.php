<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Grpc;

use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Contrib\Grpc\GrpcTransportFactory
 */
class GrpcTransportFactoryTest extends TestCase
{
    public function test_grpc_transport_create(): void
    {
        $factory = new GrpcTransportFactory();
        $transport = $factory->create('http://localhost/service/method');

        $this->assertInstanceOf(TransportInterface::class, $transport);
    }
}
