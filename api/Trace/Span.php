<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Span extends SpanStatus, SpanKind
{
    public function getSpanName(): string;
    public function getContext(): SpanContext;
    public function getParent(): ?SpanContext;
    public function getStartTimestamp(): string;
    public function getEndTimestamp(): ?string;
    public function getAttributes(): Attributes;
    public function getLinks(): Links;
    public function getEvents(): Events;
    public function getStatus(): SpanStatus;

    /**
     * Attributes SHOULD preserve the order in which they're set. Setting an attribute with the same key as an existing
     * attribute SHOULD overwrite the existing attribute's value.
     * @param string $key
     * @param bool|int|float|string|array $value Note: the array MUST be homogeneous, i.e. it MUST NOT contain values
     *                                           of different types.
     * @return Span Must return $this to allow setting multiple attributes at once in a chain
     */
    public function setAttribute(string $key, $value): Span;

    /**
     * @param string $name
     * @param Attributes|null $attributes
     * @param string|null $timestamp
     * @return Span Must return $this to allow setting multiple attributes at once in a chain.
     */
    public function addEvent(string $name, ?Attributes $attributes = null, ?string $timestamp = null): Span;

    /**
     * @param SpanContext $context
     * @param Attributes|null $attributes
     * @return Span Must return $this to allow setting multiple links at once in a chain.
     */
    public function addLink(SpanContext $context, ?Attributes $attributes = null): Span;

    /**
     * Calling this method is highly discouraged; the name should be set on creation and left alone.
     * @param string $name
     * @return Span Must return $this
     */
    public function updateName(string $name): Span;

    // TODO: addLazyEvent
    // TODO: end(), though why is this allowed on the span? I thought Tracers were responsible for this?
}
