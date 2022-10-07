<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Grpc;

use Exception;
use const Grpc\STATUS_OK;
use const Grpc\STATUS_UNAVAILABLE;
use Grpc\UnaryCall;
use OpenTelemetry\Contrib\Grpc\GrpcTransport;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceResponse;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use UnexpectedValueException;

/**
 * @covers \OpenTelemetry\Contrib\Grpc\GrpcTransport
 * Note that real ExportTraceService* objects are used, since mocking them segfaults with ext-protobuf
 */
final class GrpcTransportTest extends TestCase
{
    private GrpcTransport $transport;
    private ExportTraceServiceResponse $response;
    private MockObject $call;
    private object $status;

    public function setUp(): void
    {
        $this->status = (object) ['code' => STATUS_OK];
        $this->call = $this->createMock(UnaryCall::class);
        $this->response = new ExportTraceServiceResponse();
        $client = $this->createMock(TraceServiceClient::class);
        $client->method('Export')->willReturn($this->call);
        $this->transport = new GrpcTransport($client);
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

    public function test_send_failure_with_grpc_exception(): void
    {
        $this->call->method('wait')->willThrowException(new \Exception('dummy exception'));
        $future = $this->transport->send('some.payload', TransportInterface::CONTENT_TYPE_PROTOBUF);
        $this->assertInstanceOf(ErrorFuture::class, $future);
        $this->expectException(\Exception::class);
        $future->await();
    }

    public function test_send_success(): void
    {
        $this->call->method('wait')->willReturn([$this->response, $this->status]);
        $this->response->mergeFromString('');
        $future = $this->transport->send('', TransportInterface::CONTENT_TYPE_PROTOBUF);
        $this->assertSame('', $future->await());
    }

    public function test_send_failure_with_invalid_payload(): void
    {
        $future = $this->transport->send('invalid.payload', TransportInterface::CONTENT_TYPE_PROTOBUF);
        $this->assertInstanceOf(ErrorFuture::class, $future);
    }

    public function test_send_failure_with_not_ok_status(): void
    {
        $this->status->code = STATUS_UNAVAILABLE;
        $this->status->details = 'error.detail';
        $this->call->method('wait')->willReturn([$this->response, $this->status]);
        $future = $this->transport->send('', TransportInterface::CONTENT_TYPE_PROTOBUF);
        $this->assertInstanceOf(ErrorFuture::class, $future);
    }

    public function test_send_failure_with_exception_in_grpc_call(): void
    {
        $this->call->method('wait')->willThrowException(new RuntimeException('grpc exception'));
        $future = $this->transport->send('', TransportInterface::CONTENT_TYPE_PROTOBUF);
        $this->assertInstanceOf(ErrorFuture::class, $future);
    }
}
