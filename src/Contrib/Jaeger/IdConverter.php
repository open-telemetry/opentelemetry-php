<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

class IdConverter
{
    public static function convertOtelToJaegerTraceIds(string $traceId): array
    {
        $traceIdLow = intval(substr($traceId, 0, 16), 16);
        $traceIdHigh = intval(substr($traceId, 16, 32), 16);

        return [
            'traceIdLow' => $traceIdLow,
            'traceIdHigh' => $traceIdHigh,
        ];
    }

    public static function convertOtelToJaegerSpanId(string $spanId): int
    {
        return intval($spanId, 16);
    }
}
