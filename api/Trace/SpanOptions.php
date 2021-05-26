<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

/**
 * The SpanOptions implementation is intended to be tightly coupled with the Span and Tracer implementations.
 * Hopefully the Span object can implement SpanOptions so that the toSpan() call is almost a no-op, but prevents
 * the rest of the SpanOptions API from being used after toSpan() is called.
 */
interface SpanOptions
{
    public function setSpanName(string $name): SpanOptions;
    /** should default to INTERNAL if not called */
    public function setSpanKind(int $spanKind): SpanOptions;
    public function setParentContext(SpanContext $span): SpanOptions;
    public function setParentSpan(Span $span): SpanOptions;
    public function addAttributes(Attributes $attributes): SpanOptions;
    public function addLinks(Links $links): SpanOptions;

    /**
     * This should only be used if the creation time has already passed; will set timestamp to current time by default
     * @param int $timestamp
     * @return SpanOptions
     */
    public function addStartTimestamp(int $timestamp): SpanOptions;
    /**
     * This should only be used if the creation time has already passed; will set to current monotonic clock
     * value by default
     * @param int $now
     * @return SpanOptions
     */
    public function addStart(int $now): SpanOptions;

    public function toSpan(): Span;
    // todo: how do we want to optionally let people make these spans active? bool arg, setActive, or toActiveSpan?
    public function toActiveSpan(): Span;
}
