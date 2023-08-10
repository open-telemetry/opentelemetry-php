<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#spancontext
 */
interface SpanContextInterface
{
    public static function createFromRemoteParent(string $traceId, string $spanId, int $traceFlags = TraceFlags::DEFAULT, ?TraceStateInterface $traceState = null): SpanContextInterface;
    public static function getInvalid(): SpanContextInterface;
    public static function create(string $traceId, string $spanId, int $traceFlags = TraceFlags::DEFAULT, ?TraceStateInterface $traceState = null): SpanContextInterface;

    /** @psalm-mutation-free */
    public function getTraceId(): string;
    public function getTraceIdBinary(): string;

    /** @psalm-mutation-free */
    public function getSpanId(): string;
    public function getSpanIdBinary(): string;
    public function getTraceFlags(): int;
    public function getTraceState(): ?TraceStateInterface;
    public function isValid(): bool;
    public function isRemote(): bool;
    public function isSampled(): bool;
}
