<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\ContextInterface;

/**
 * Obtained from a {@see TracerInterface} and used to construct a {@see SpanInterface}.
 */
interface SpanBuilderInterface
{
    /**
     * Sets the parent `Context`.
     *
     * @param ContextInterface|false|null $context the parent context, null to use the
     *        current context, false to set no parent
     * @return SpanBuilderInterface this span builder
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/trace/api.md#span-creation
     */
    public function setParent(ContextInterface|false|null $context): SpanBuilderInterface;

    public function addLink(SpanContextInterface $context, iterable $attributes = []): SpanBuilderInterface;
    public function setAttribute(string $key, mixed $value): SpanBuilderInterface;
    public function setAttributes(iterable $attributes): SpanBuilderInterface;

    /**
     * Sets an explicit start timestamp for the newly created {@see SpanInterface}.
     * The provided *$timestamp* is assumed to be in nanoseconds.
     *
     * Defaults to the timestamp when {@see SpanBuilderInterface::startSpan} was called if not explicitly set.
     */
    public function setStartTimestamp(int $timestampNanos): SpanBuilderInterface;

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
