<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

interface ContentTypes
{
    public const PROTOBUF = 'application/x-protobuf';
    public const JSON     = 'application/json';
    public const NDJSON   = 'application/x-ndjson';
}
