<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use AssertionError;
use function base64_decode;
use function bin2hex;
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

    public static function getDefault(): ProtobufSerializer
    {
        return new self(self::PROTOBUF);
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

    public function serializeTraceId(string $traceId): string
    {
        switch ($this->contentType) {
            case self::PROTOBUF:
                return $traceId;
            case self::JSON:
            case self::NDJSON:
                return base64_decode(bin2hex($traceId));
            default:
                throw new AssertionError();
        }
    }

    public function serializeSpanId(string $spanId): string
    {
        switch ($this->contentType) {
            case self::PROTOBUF:
                return $spanId;
            case self::JSON:
            case self::NDJSON:
                return base64_decode(bin2hex($spanId));
            default:
                throw new AssertionError();
        }
    }

    public function serialize(Message $message): string
    {
        switch ($this->contentType) {
            case self::PROTOBUF:
                return $message->serializeToString();
            case self::JSON:
                //@todo https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/protocol/otlp.md#json-protobuf-encoding
                return $message->serializeToJsonString();
            case self::NDJSON:
                return $message->serializeToJsonString() . "\n";
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
                // @phan-suppress-next-line PhanParamTooManyInternal
                $message->mergeFromJsonString($payload, true);

                break;
            default:
                throw new AssertionError();
        }
    }
}
