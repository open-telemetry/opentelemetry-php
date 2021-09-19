<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface SpanContext
{
    public const TRACE_FLAG_SAMPLED = 1;

    public static function getInvalid(): SpanContext;

    /** @psalm-mutation-free */
    public function getTraceId(): string;

    /** @psalm-mutation-free */
    public function getSpanId(): string;
    public function getTraceFlags(): int;
    public function getTraceState(): ?TraceState;
    public function isValid(): bool;
    public function isRemote(): bool;
    public function isSampled(): bool;
}
