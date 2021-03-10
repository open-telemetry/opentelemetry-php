<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Tracer
{
    public function getActiveSpan(): Span;

    public function startAndActivateSpan(string $name, int $spanKind = SpanKind::KIND_INTERNAL): Span;
    public function startSpanWithOptions(string $name): SpanOptions;

    public function setActiveSpan(Span $span): void;

    // "finished vs "active" is a bit murky to me
    public function finishSpan(Span $span, ?int $timestamp = null): void;
    public function deactivateActiveSpan(): void;
    public function endActiveSpan(?int $timestamp = null);
}
