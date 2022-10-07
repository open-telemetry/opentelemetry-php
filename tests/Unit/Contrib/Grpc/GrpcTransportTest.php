<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Grpc;

use Exception;
use const Grpc\STATUS_OK;
use const Grpc\STATUS_UNAVAILABLE;
use Grpc\UnaryCall;
use Mockery;
use Mockery\MockInterface;
use OpenTelemetry\Contrib\Grpc\GrpcTransport;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceResponse;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use UnexpectedValueException;

/**
 * @covers \OpenTelemetry\Contrib\Grpc\GrpcTransport
 * @runTestsInSeparateProcesses (because of Mockery overloading)
 * @preserveGlobalState disabled
 */
final class GrpcTransportTest extends TestCase
{
    private GrpcTransport $transport;
    private MockObject $client;
    private MockInterface $request;
    private MockObject $response;
    private MockObject $call;
    private object $status;

    public function setUp(): void
    {
        $this->status = (object) ['code' => STATUS_OK];
        $this->call = $this->createMock(UnaryCall::class);
        $this->request = Mockery::mock('overload:' . ExportTraceServiceRequest::class);
        $this->response = $this->createMock(ExportTraceServiceResponse::class);
        $this->client = $this->createMock(TraceServiceClient::class);
        $this->client->method('Export')->willReturn($this->call);
        $this->transport = new GrpcTransport($this->client, []);
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

        $response = $this->transport->send('', GrpcTransport::PROTOCOL_PROTOBUF);

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
        $this->request->allows(['mergeFromString' => null]);
        $this->call->method('wait')->willThrowException(new RuntimeException('dummy exception'));
        $future = $this->transport->send('some.payload', GrpcTransport::PROTOCOL_PROTOBUF);
        $this->assertInstanceOf(ErrorFuture::class, $future);
        $this->expectException(RuntimeException::class);
        $future->await();
    }

    public function test_send_success(): void
    {
        $this->call->method('wait')->willReturn([$this->response, $this->status]);
        $this->request->allows(['mergeFromString' => null]);
        $this->response->method('serializeToString')->willReturn('serialized.to.string');
        $future = $this->transport->send('', GrpcTransport::PROTOCOL_PROTOBUF);
        $this->assertSame('serialized.to.string', $future->await());
    }

    public function test_send_failure_with_invalid_payload(): void
    {
        $this->request->shouldReceive('mergeFromString')->andThrow(new RuntimeException('serialization error'));
        $future = $this->transport->send('', GrpcTransport::PROTOCOL_PROTOBUF);
        $this->assertInstanceOf(ErrorFuture::class, $future);
    }

    public function test_send_failure_with_not_ok_status(): void
    {
        $this->status->code = STATUS_UNAVAILABLE;
        $this->status->details = 'error.detail';
        $this->call->method('wait')->willReturn([$this->response, $this->status]);
        $this->request->allows(['mergeFromString' => null]);
        $this->response->method('serializeToString')->willReturn('serialized.to.string');
        $future = $this->transport->send('', GrpcTransport::PROTOCOL_PROTOBUF);
        $this->assertInstanceOf(ErrorFuture::class, $future);
    }

    public function test_send_failure_with_exception_in_grpc_call(): void
    {
        $this->call->method('wait')->willThrowException(new RuntimeException('grpc exception'));
        $this->request->allows(['mergeFromString' => null]);
        $this->response->method('serializeToString')->willReturn('serialized.to.string');
        $future = $this->transport->send('', GrpcTransport::PROTOCOL_PROTOBUF);
        $this->assertInstanceOf(ErrorFuture::class, $future);
    }
}
