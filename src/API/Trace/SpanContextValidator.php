<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use function strlen;
use function strtolower;

class SpanContextValidator
{
    public const VALID_SPAN = '/^[0-9a-f]{16}$/';
    public const VALID_TRACE = '/^[0-9a-f]{32}$/';
    public const INVALID_SPAN = '0000000000000000';
    public const INVALID_TRACE = '00000000000000000000000000000000';
    public const SPAN_LENGTH = 16;
    public const TRACE_LENGTH = 32;
    public const SPAN_LENGTH_BYTES = 8;

    /**
     * @return bool Returns a value that indicates whether a trace id is valid
     */
    public static function isValidTraceId(string $traceId): bool
    {
        return ctype_xdigit($traceId) && strlen($traceId) === self::TRACE_LENGTH && $traceId !== self::INVALID_TRACE && $traceId === strtolower($traceId);
    }

    /**
     * @return bool Returns a value that indicates whether a span id is valid
     */
    public static function isValidSpanId(string $spanId): bool
    {
        return ctype_xdigit($spanId) && strlen($spanId) === self::SPAN_LENGTH && $spanId !== self::INVALID_SPAN && $spanId === strtolower($spanId);
    }
}
