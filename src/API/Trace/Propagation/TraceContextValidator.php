<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace\Propagation;

use function strlen;

class TraceContextValidator
{
    public const TRACE_FLAG_LENGTH = 2;
    public const TRACE_VERSION_REGEX = '/^(?!ff)[\da-f]{2}$/';

    public static function isValidTraceVersion(string $traceVersion): bool
    {
        return 1 === preg_match(self::TRACE_VERSION_REGEX, $traceVersion);
    }

    /**
     * @return bool Returns a value that indicates whether trace flag is valid
     * TraceFlags must be exactly 1 bytes (1 char) representing a bit field
     */
    public static function isValidTraceFlag(string $traceFlag): bool
    {
        return ctype_xdigit($traceFlag) && strlen($traceFlag) === self::TRACE_FLAG_LENGTH;
    }
}
