<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

use OpenTelemetry\Context\Context;

/**
 * Obtained from a {@see Tracer} and used to construct a {@see Span}.
 *
 * NOTE: A span builder may only be used to construct a single span.
 * Calling {@see SpanBuilder::startSpan} multiple times will lead to undefined behavior.
 */
interface SpanBuilder
{
    /**
     * Sets the parent {@see Context} to use.
     *
     * If no {@see Span} is available in the provided context, the resulting span will become a root span,
     * as if {@see SpanBuilder::setNoParent} was called.
     *
     * Defaults to {@see Context::getCurrent} when {@see SpanBuilder::startSpan} was called if not explicitly set.
     */
    public function setParent(Context $parentContext): SpanBuilder;

    /**
     * Makes the to be created {@see Span} a root span of a new trace.
     */
    public function setNoParent(): SpanBuilder;
    public function addLink(SpanContext $context, Attributes $attributes = null): SpanBuilder;
    public function setAttribute(string $key, $value): SpanBuilder;
    public function setAttributes(Attributes $attributes): SpanBuilder;

    /**
     * Sets an explicit start timestamp for the newly created {@see Span}.
     * The provided *$timestamp* is assumed to be in nanoseconds.
     *
     * Defaults to the timestamp when {@see SpanBuilder::startSpan} was called if not explicitly set.
     */
    public function setStartTimestamp(int $timestamp): SpanBuilder;

    /**
     * @param SpanKind::KIND_* $spanKind
     */
    public function setSpanKind(int $spanKind): SpanBuilder;

    /**
     * Starts and returns a new {@see Span}.
     *
     * The user _MUST_ manually end the span by calling {@see Span::end}.
     *
     * This method does _NOT_ automatically install the span into the current context.
     * The user is responsible for calling {@see Span::activate} when they wish to do so.
     */
    public function startSpan(): Span;
}
