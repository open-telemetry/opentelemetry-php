<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface SpanContext
{
    const TRACE_FLAG_SAMPLED = 1;

    public function getTraceId(): int;
    public function getSpanId(): int;
    public function getTraceFlags(): int;
    public function getTraceState(): ?TraceState;
    public function isValidContext(): bool;
    public function isRemoteContext(): bool;
    public function isSampled(): bool;
}
