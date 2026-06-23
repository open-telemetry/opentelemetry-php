<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use OpenTelemetry\Contrib\Otlp\ContentTypes;
use OpenTelemetry\Contrib\Otlp\Protocols;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(Protocols::class)]
class ProtocolsTest extends TestCase
{
    #[DataProvider('protocolProvider')]
    public function test_content_type(string $protocol, string $expected): void
    {
        $this->assertSame($expected, Protocols::contentType($protocol));
    }

    public static function protocolProvider(): array
    {
        return [
            'grpc' => [Protocols::GRPC, ContentTypes::PROTOBUF],
            'http/protobuf' => [Protocols::HTTP_PROTOBUF, ContentTypes::PROTOBUF],
            'http/json' => [Protocols::HTTP_JSON, ContentTypes::JSON],
            'http/ndjson' => [Protocols::HTTP_NDJSON, ContentTypes::NDJSON],
        ];
    }

    public function test_validate_throws_for_unknown_protocol(): void
    {
        $this->expectException(UnexpectedValueException::class);
        Protocols::validate('unknown');
    }

    public function test_validate_succeeds_for_known_protocol(): void
    {
        Protocols::validate(Protocols::GRPC);
        $this->assertTrue(true);
    }

    public function test_content_type_throws_for_unknown_protocol(): void
    {
        $this->expectException(UnexpectedValueException::class);
        Protocols::contentType('unknown');
    }
}
