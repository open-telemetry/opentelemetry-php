<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use AssertionError;
use Exception;
use Google\Protobuf\Internal\Message;
use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use function sprintf;

/**
 * @internal
 *
 * @psalm-type SUPPORTED_CONTENT_TYPES = self::PROTOBUF|self::JSON|self::NDJSON
 */
final class ProtobufSerializer
{
    private const PROTOBUF = 'application/x-protobuf';
    private const JSON = 'application/json';
    private const NDJSON = 'application/x-ndjson';

    private string $contentType;

    private function __construct(string $contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @psalm-param TransportInterface<SUPPORTED_CONTENT_TYPES> $transport
     */
    public static function forTransport(TransportInterface $transport): ProtobufSerializer
    {
        switch ($contentType = $transport->contentType()) {
            case self::PROTOBUF:
            case self::JSON:
            case self::NDJSON:
                return new self($contentType);
            default:
                throw new InvalidArgumentException(sprintf('Not supported content type "%s"', $contentType));
        }
    }

    public function serialize(Message $message): string
    {
        switch ($this->contentType) {
            case self::PROTOBUF:
                return $message->serializeToString();
            case self::JSON:
                return self::fixJsonOutput($message->serializeToJsonString());
            case self::NDJSON:
                return self::fixJsonOutput($message->serializeToJsonString()) . "\n";
            default:
                throw new AssertionError();
        }
    }

    /**
     * @throws Exception
     */
    public function hydrate(Message $message, string $payload): void
    {
        switch ($this->contentType) {
            case self::PROTOBUF:
                $message->mergeFromString($payload);

                break;
            case self::JSON:
            case self::NDJSON:
                $message->mergeFromJsonString($payload);

                break;
            default:
                throw new AssertionError();
        }
    }

    /**
     * JSON encoding of traceid + spanid requires the hexadecimal-encoded values, which is different to protobuf
     * which requires base64-encoded binary representation. This finds all traceId + spanId JSON fields, and
     * reverses the operation to get back to hexadecimal.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.20.0/specification/protocol/otlp.md#json-protobuf-encoding
     * @todo "Values of enum fields MUST be encoded as integer values"
     */
    public static function fixJsonOutput(string $json): string
    {
        $patterns = [
            "|\"traceId\"\:[\s]*\"([A-Za-z0-9+/]*={0,2})\"|",
            "|\"spanId\"\:[\s]*\"([A-Za-z0-9+/]*={0,2})\"|",
            "|\"parentSpanId\"\:[\s]*\"([A-Za-z0-9+/]*={0,2})\"|",
        ];

        return preg_replace_callback($patterns, fn ($matches) => str_replace($matches[1], self::base64BinaryToHex($matches[1]), $matches[0]), $json);
    }

    /**
     * Reverse protobuf-encoding of binary value to its hexadecimal representation
     */
    public static function base64BinaryToHex(string $value): string
    {
        return bin2hex(base64_decode($value));
    }
}
