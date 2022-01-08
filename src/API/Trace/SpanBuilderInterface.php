<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\Context;

/**
 * Obtained from a {@see TracerInterface} and used to construct a {@see SpanInterface}.
 *
 * NOTE: A span builder may only be used to construct a single span.
 * Calling {@see SpanBuilderInterface::startSpan} multiple times will lead to undefined behavior.
 */
interface SpanBuilderInterface
{
    /**
     * Sets the parent {@see Context} to use.
     *
     * If no {@see SpanInterface} is available in the provided context, the resulting span will become a root span,
     * as if {@see SpanBuilderInterface::setNoParent} was called.
     *
     * Defaults to {@see Context::getCurrent} when {@see SpanBuilderInterface::startSpan} was called if not explicitly set.
     */
    public function setParent(Context $parentContext): SpanBuilderInterface;

    /**
     * Makes the to be created {@see SpanInterface} a root span of a new trace.
     */
    public function setNoParent(): SpanBuilderInterface;

    /**
     * @param iterable<non-empty-string, bool|int|float|string|array|null> $attributes
     */
    public function addLink(SpanContextInterface $context, iterable $attributes = []): SpanBuilderInterface;

    /**
     * @param non-empty-string $key
     * @param bool|int|float|string|array|null $value
     */
    public function setAttribute(string $key, $value): SpanBuilderInterface;

    /**
     * @param iterable<non-empty-string, bool|int|float|string|array|null> $attributes
     */
    public function setAttributes(iterable $attributes): SpanBuilderInterface;

    /**
     * Sets an explicit start timestamp for the newly created {@see SpanInterface}.
     * The provided *$timestamp* is assumed to be in nanoseconds.
     *
     * Defaults to the timestamp when {@see SpanBuilderInterface::startSpan} was called if not explicitly set.
     */
    public function setStartTimestamp(int $timestamp): SpanBuilderInterface;

    /**
     * @psalm-param SpanKind::KIND_* $spanKind
     */
    public function setSpanKind(int $spanKind): SpanBuilderInterface;

    /**
     * Starts and returns a new {@see SpanInterface}.
     *
     * The user _MUST_ manually end the span by calling {@see SpanInterface::end}.
     *
     * This method does _NOT_ automatically install the span into the current context.
     * The user is responsible for calling {@see SpanInterface::activate} when they wish to do so.
     */
    public function startSpan(): SpanInterface;
}
