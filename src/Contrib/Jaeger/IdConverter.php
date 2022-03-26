<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use Brick\Math\BigInteger;

/**
 * An implementation of the section of the spec around converting OTEL's span/trace id representations to Thrift's format
 * https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/trace/sdk_exporters/jaeger.md#ids
 */
class IdConverter
{
    public static function convertOtelToJaegerTraceIds(string $traceId): array
    {
        $traceIdLow = self::convertHexStringToSignedInt(substr($traceId, 0, 16));
        $traceIdHigh = self::convertHexStringToSignedInt(substr($traceId, 16, 32));

        return [
            'traceIdLow' => $traceIdLow,
            'traceIdHigh' => $traceIdHigh,
        ];
    }

    public static function convertOtelToJaegerSpanId(string $spanId): int
    {
        return self::convertHexStringToSignedInt($spanId);
    }

    private static function convertHexStringToSignedInt(string $hexString): int
    {
        //This is using high precision arithmetic because PHP by default won't do subtraction correctly for large integers (e.g. (2^63 - 1) - (2^63))
        $hexStringAsInteger = BigInteger::fromBase($hexString, 16);

        $shiftedInteger = $hexStringAsInteger->minus(BigInteger::fromBase('8000000000000000', 16)); //The number being subtracted is 2^63

        $signedInt = $shiftedInteger->toInt();

        return $signedInt;
    }
}
