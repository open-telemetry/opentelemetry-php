<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

/**
 * @todo enum (php >= 8.1)
 */
interface ContentTypes
{
    public const PROTOBUF = 'application/x-protobuf';
    public const JSON     = 'application/json';
    public const NDJSON   = 'application/x-ndjson';
}
