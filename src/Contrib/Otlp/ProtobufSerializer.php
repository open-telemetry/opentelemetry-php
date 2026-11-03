<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use function base64_decode;
use function bin2hex;
use Exception;
use Google\Protobuf\Internal\Message;
use Google\Protobuf\PrintOptions;
use InvalidArgumentException;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use function sprintf;

/**
 * @internal
 */
final class ProtobufSerializer
{
    use LogsMessagesTrait;

    private function __construct(private readonly string $contentType)
    {
    }

    public static function getDefault(): ProtobufSerializer
    {
        return new self(ContentTypes::PROTOBUF);
    }

    /**
     * @param TransportInterface<ContentTypes::*> $transport
     */
    public static function forTransport(TransportInterface $transport): ProtobufSerializer
    {
        return match ($contentType = $transport->contentType()) {
            ContentTypes::PROTOBUF, ContentTypes::JSON, ContentTypes::NDJSON => new self($contentType),
            default => throw new InvalidArgumentException(sprintf('Not supported content type "%s"', $contentType)),
        };
    }

    public function serializeTraceId(string $traceId): string
    {
        // @phpstan-ignore-next-line
        return match ($this->contentType) {
            ContentTypes::PROTOBUF => $traceId,
            ContentTypes::JSON, ContentTypes::NDJSON => base64_decode(bin2hex($traceId)),
        };
    }

    public function serializeSpanId(string $spanId): string
    {
        // @phpstan-ignore-next-line
        return match ($this->contentType) {
            ContentTypes::PROTOBUF => $spanId,
            ContentTypes::JSON, ContentTypes::NDJSON => base64_decode(bin2hex($spanId)),
        };
    }

    public function serialize(Message $message): string
    {
        // @phpstan-ignore-next-line
        return match ($this->contentType) {
            ContentTypes::PROTOBUF => $message->serializeToString(),
            ContentTypes::JSON => self::serializeToJsonString($message),
            ContentTypes::NDJSON => self::serializeToJsonString($message) . "\n",
        };
    }

    /**
     * @phan-suppress PhanParamTooManyInternal (@see https://github.com/phan/phan/pull/4840)
     * @throws Exception
     */
    public function hydrate(Message $message, string $payload): void
    {
        // @phpstan-ignore-next-line
        match ($this->contentType) {
            ContentTypes::PROTOBUF => $message->mergeFromString($payload),
            ContentTypes::JSON, ContentTypes::NDJSON => $message->mergeFromJsonString($payload, true),
        };
    }

    /**
     * Enum values are encoded as integers, not names.
     *
     * @see https://github.com/open-telemetry/opentelemetry-proto/blob/v1.11.1/docs/specification.md#json-protobuf-encoding
     */
    private static function serializeToJsonString(Message $message): string
    {
        return $message->serializeToJsonString(PrintOptions::ALWAYS_PRINT_ENUMS_AS_INTS);
    }
}
