<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use Google\Protobuf\Internal\Message;
use OpenTelemetry\SDK\Common\Environment\KnownValues;
use UnexpectedValueException;

class Converter
{
    public const CONTENT_TYPES = [
        KnownValues::VALUE_GRPC => 'application/x-protobuf',
        KnownValues::VALUE_HTTP_PROTOBUF => 'application/x-protobuf',
        KnownValues::VALUE_HTTP_JSON => 'application/json',
        KnownValues::VALUE_HTTP_NDJSON => 'application/x-ndjson',
    ];

    public static function encode(Message $payload, string $protocol): string
    {
        switch ($protocol) {
            case KnownValues::VALUE_HTTP_PROTOBUF:
            case KnownValues::VALUE_GRPC:
                return $payload->serializeToString();
            case KnownValues::VALUE_HTTP_NDJSON:
                return $payload->serializeToJsonString() . "\n";
            case KnownValues::VALUE_HTTP_JSON:
                return $payload->serializeToJsonString();
            default:
                throw new UnexpectedValueException('Unknown protocol: ' . $protocol);
        }
    }

    /**
     * @todo json encoding to spec? see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/protocol/otlp.md#json-protobuf-encoding
     */
    public static function decode(Message $response, string $payload, string $protocol): void
    {
        switch ($protocol) {
            case KnownValues::VALUE_HTTP_PROTOBUF:
            case KnownValues::VALUE_GRPC:
                $response->mergeFromString($payload);

                break;
            case KnownValues::VALUE_HTTP_JSON:
                $response->mergeFromJsonString($payload);

                break;
            default:
                throw new UnexpectedValueException('Unknown protocol: ' . $protocol);
        }
    }

    public static function contentType(string $protocol): string
    {
        if (!array_key_exists($protocol, self::CONTENT_TYPES)) {
            throw new UnexpectedValueException('Unknown protocol: ' . $protocol);
        }

        return self::CONTENT_TYPES[$protocol];
    }
}
