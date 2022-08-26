<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use function strlen;
use function strtolower;

class SpanContextValidator
{
    public const INVALID_TRACE = '00000000000000000000000000000000';
    public const INVALID_SPAN = '0000000000000000';
    public const SPAN_LENGTH = 16;
    public const TRACE_FLAG_LENGTH = 2;
    public const TRACE_LENGTH = 32;
    public const TRACE_VERSION_REGEX = '/^(?!ff)[\da-f]{2}$/';

    /**
     * @param string $traceVersion
     * @return bool Returns a value that indicates whether a trace version is valid.
     */
    public static function isValidTraceVersion(string $traceVersion): bool
    {
        return 1 === preg_match(self::TRACE_VERSION_REGEX, $traceVersion);
    }

    /**
     * @return bool Returns a value that indicates whether a trace id is valid
     */
    public static function isValidTraceId($traceId): bool
    {
        return ctype_xdigit($traceId) && strlen($traceId) === self::TRACE_LENGTH && $traceId !== self::INVALID_TRACE && $traceId === strtolower($traceId);
    }

    /**
     * @return bool Returns a value that indicates whether a span id is valid
     */
    public static function isValidSpanId($spanId): bool
    {
        return ctype_xdigit($spanId) && strlen($spanId) === self::SPAN_LENGTH && $spanId !== self::INVALID_SPAN && $spanId === strtolower($spanId);
    }

    /**
     * @return bool Returns a value that indicates whether trace flag is valid
     * TraceFlags must be exactly 1 bytes (1 char) representing a bit field
     */
    public static function isValidTraceFlag($traceFlag): bool
    {
        return ctype_xdigit($traceFlag) && strlen($traceFlag) === self::TRACE_FLAG_LENGTH;
    }
}
