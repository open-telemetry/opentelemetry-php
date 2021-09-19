<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

use OpenTelemetry\Context\Context;

interface SpanBuilder
{
    public function setParent(Context $parentContext): SpanBuilder;
    public function setNoParent(): SpanBuilder;
    public function addLink(SpanContext $context, Attributes $attributes = null): SpanBuilder;
    public function setAttribute(string $key, $value): SpanBuilder;
    public function setAttributes(Attributes $attributes): SpanBuilder;

    /**
     * @param SpanKind::KIND_* $spanKind
     */
    public function setSpanKind(int $spanKind): SpanBuilder;
    public function setStartTimestamp(int $timestamp): SpanBuilder;
    public function startSpan(): Span;
}
