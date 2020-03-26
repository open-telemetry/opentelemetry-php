<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface SpanContext
{
    public function getTraceId(): string;
    public function getSpanId(): string;
    public function getTraceFlags(): int;
    public function getTracestate(): array;
    public function IsValidContext(): bool;
    public function IsRemoteContext(): bool;
}
