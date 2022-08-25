<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

class SpanContextFactory
{
    public const TRACE_FLAG_DEFAULT = 0x00;

    /** @inheritDoc */
    public static function createFromRemoteParent(string $traceId, string $spanId, int $traceFlags= self::TRACE_FLAG_DEFAULT, ?TraceStateInterface $traceState = null): SpanContextInterface
    {
        return SpanContext::builder(
            $traceId,
            $spanId,
            true,
            $traceFlags,
            $traceState
        );
    }

    /** @inheritDoc */
    public static function create(string $traceId, string $spanId, int $traceFlags= self::TRACE_FLAG_DEFAULT, ?TraceStateInterface $traceState = null): SpanContextInterface
    {
        return SpanContext::builder(
            $traceId,
            $spanId,
            false,
            $traceFlags,
            $traceState
        );
    }
}
