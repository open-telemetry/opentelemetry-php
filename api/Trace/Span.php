<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

use Exception;

interface Span extends SpanStatus, SpanKind
{
    public function getSpanName(): string;
    public function getContext(): SpanContext;
    public function getParent(): ?SpanContext;

    /**
     * Returns Epoch timestamp value (RealtimeClock) when the Span was created
     * @return int
     */
    public function getStartEpochTimestamp(): int;

    /**
     * Returns system time clock value (MonotonicClock) when the Span was created
     * @return int
     */
    public function getStart(): int;

    /**
     * Returns system time clock value (MonotonicClock) when the Span was stopped
     * @return int|null
     */
    public function getEnd(): ?int;

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
     * @param int $timestamp
     * @param Attributes|null $attributes
     * @return Span Must return $this to allow setting multiple attributes at once in a chain.
     */
    public function addEvent(string $name, int $timestamp, ?Attributes $attributes = null): Span;

    /**
     * @param SpanContext $context
     * @param Attributes|null $attributes
     * @return Span Must return $this to allow setting multiple links at once in a chain.
     */
    public function addLink(SpanContext $context, ?Attributes $attributes = null): Span;

    /**
     *
     * @param Exception $exception
     * @return Span Must return $this to allow setting multiple attributes at once in a chain.
     */
    public function recordException(Exception $exception): Span;

    /**
     * Calling this method is highly discouraged; the name should be set on creation and left alone.
     * @param string $name
     * @return Span Must return $this
     */
    public function updateName(string $name): Span;

    /**
     * Sets the Status of the Span. If used, this will override the default Span status, which is OK.
     * Only the value of the last call will be recorded, and implementations are free to ignore previous calls.
     * @param string $code
     * @param string|null $description
     * @return Span Must return $this
     */
    public function setSpanStatus(string $code, ?string $description = null): Span;

    /**
     * @param int|null $timestamp
     * @return Span Must return $this
     */
    public function end(int $timestamp = null): Span;

    public function isRecording(): bool;

    public function isSampled(): bool;
}
