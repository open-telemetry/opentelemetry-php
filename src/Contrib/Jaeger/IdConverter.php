<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

/**
 * An implementation of the section of the spec around converting OTEL's span/trace id representations to Thrift's format
 * https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/trace/sdk_exporters/jaeger.md#ids
 */
class IdConverter
{
    public static function convertOtelToJaegerTraceIds(string $traceId): array
    {
        $traceIdLow = self::convert16CharHexStringToSignedInt(substr($traceId, 16, 32));
        $traceIdHigh = self::convert16CharHexStringToSignedInt(substr($traceId, 0, 16));

        return [
            'traceIdLow' => $traceIdLow,
            'traceIdHigh' => $traceIdHigh,
        ];
    }

    public static function convertOtelToJaegerSpanId(string $spanId): int
    {
        return self::convert16CharHexStringToSignedInt($spanId);
    }

    /**
     * PHP has the limitation to correctly convert int64 from the 16 character hex only
     * @param string $hex
     * @return int
     */
    private static function convert16CharHexStringToSignedInt(string $hex): int
    {
        $hi = intval(substr($hex, -16, -8), 16);
        $lo = intval(substr($hex, -8, 8), 16);

        return $hi << 32 | $lo;
    }
}
