<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use OpenTelemetry\Contrib\Otlp\ProtobufSerializer;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceResponse;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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

    public function test_rejects_protobuf_extension_older_than_4_31(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('4.30.2');

        ProtobufSerializer::ensureSupportedExtension('4.30.2');
    }

    #[DataProvider('supportedExtensionProvider')]
    public function test_accepts_supported_protobuf_extension(string|false $version): void
    {
        $this->expectNotToPerformAssertions();

        ProtobufSerializer::ensureSupportedExtension($version);
    }

    public static function supportedExtensionProvider(): array
    {
        return [
            'not loaded' => [false],
            '4.31.0' => ['4.31.0'],
            '5.x' => ['5.33.0'],
        ];
    }
}
