<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\SDK\Common\Environment\KnownValues;
use UnexpectedValueException;

class Protocols
{
    public const GRPC = KnownValues::VALUE_GRPC;
    public const HTTP_PROTOBUF = KnownValues::VALUE_HTTP_PROTOBUF;
    public const HTTP_JSON = KnownValues::VALUE_HTTP_JSON;
    public const HTTP_ND_JSON = KnownValues::VALUE_HTTP_NDJSON;
    private const PROTOCOLS = [
        self::GRPC,
        self::HTTP_PROTOBUF,
        self::HTTP_JSON,
        self::HTTP_ND_JSON,
    ];

    public static function validate(string $protocol): void
    {
        if (!in_array($protocol, self::PROTOCOLS)) {
            throw new UnexpectedValueException('Unknown protocol: ' . $protocol);
        }
    }
}
