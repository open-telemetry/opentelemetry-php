<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

use OpenTelemetry\Context\ImplicitContextKeyed;
use Throwable;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#span-operations
 */
interface Span extends SpanStatus, SpanKind, ImplicitContextKeyed
{
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
     *
     * @param Throwable $exception
     * @return Span Must return $this to allow setting multiple attributes at once in a chain.
     */
    public function recordException(Throwable $exception, ?Attributes $attributes = null): Span;

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

    public function getContext(): SpanContext;

    public function isRecording(): bool;
}
