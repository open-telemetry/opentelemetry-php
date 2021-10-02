<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#spancontext
 */
interface SpanContextInterface
{
    public const TRACE_FLAG_SAMPLED = 0x01;
    public const TRACE_FLAG_DEFAULT = 0x00;

    /** @todo Implement this in the API layer */
    public static function createFromRemoteParent(string $traceId, string $spanId, int $traceFlags = self::TRACE_FLAG_DEFAULT, ?TraceStateInterface $traceState = null): SpanContextInterface;

    /** @todo Implement this in the API layer */
    public static function getInvalid(): SpanContextInterface;

    /** @todo Implement this in the API layer */
    public static function create(string $traceId, string $spanId, int $traceFlags = self::TRACE_FLAG_DEFAULT, ?TraceStateInterface $traceState = null): SpanContextInterface;

    /** @psalm-mutation-free */
    public function getTraceId(): string;

    /** @psalm-mutation-free */
    public function getSpanId(): string;
    public function getTraceFlags(): int;
    public function getTraceState(): ?TraceStateInterface;
    public function isValid(): bool;
    public function isRemote(): bool;
    public function isSampled(): bool;
}
