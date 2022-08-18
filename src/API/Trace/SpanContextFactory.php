<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

class SpanContextFactory
{
    /** @inheritDoc */
    public static function createFromRemoteParent(string $traceId, string $spanId, int $traceFlags, ?TraceStateInterface $traceState = null): SpanContextInterface
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
    public static function create(string $traceId, string $spanId, int $traceFlags, ?TraceStateInterface $traceState = null): SpanContextInterface
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
