<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use OpenTelemetry\Contrib\Otlp\ProtobufSerializer;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceResponse;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProtobufSerializer::class)]
class ProtobufSerializerTest extends TestCase
{
    public function test_empty_json_response(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->method('contentType')->willReturn('application/json');
        $serializer = ProtobufSerializer::forTransport($transport);
        $response = new ExportTraceServiceResponse();
        $serializer->hydrate($response, '{}');

        $this->assertNull($response->getPartialSuccess());
    }
}
