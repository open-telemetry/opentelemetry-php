<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Tracer
{
    public function getActiveSpan(): Span;

    public function startAndActivateSpan(string $name): Span;
    public function startSpanWithOptions(string $name): SpanOptions;

    public function setActiveSpan(Span $span): void;

    // "finished vs "active" is a bit murky to me
    public function finishSpan(Span $span, ?string $timestamp = null): void;
    public function deactivateActiveSpan(): void;
}
