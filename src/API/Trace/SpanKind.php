<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#spankind
 */
interface SpanKind
{
    public const KIND_INTERNAL = 0;
    public const KIND_CLIENT = 1;
    public const KIND_SERVER = 2;
    public const KIND_PRODUCER = 3;
    public const KIND_CONSUMER = 4;
}
