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
    const HEX_STR_FOR_2_TO_THE_POWER_63 = '8000000000000000';

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
        //This is using high precision arithmetic because PHP doesn't support integers greater than 2^63 - 1 (PHP_INT_MAX on a 64-bit system)
        //But the input 16-digit hexadecimal number can represent any number between 0 and 2^64 - 1, which built-ins like intval can't handle without capping outputs at 2^63 - 1
        $hexStringAsInteger = BigInteger::fromBase($hexString, 16);

        $shiftedInteger = $hexStringAsInteger->minus(BigInteger::fromBase(self::HEX_STR_FOR_2_TO_THE_POWER_63, 16));

        $signedInt = $shiftedInteger->toInt();

        return $signedInt;
    }
}
