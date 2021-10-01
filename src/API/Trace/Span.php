<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ImplicitContextKeyed;
use Throwable;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#span-operations
 */
interface Span extends ImplicitContextKeyed
{
    /**
     * Returns the {@see Span} from the provided *$context*,
     * falling back on {@see Span::getInvalid()} if there is no span in the provided context.
     *
     * @todo Implement this in the API layer
     */
    public static function fromContext(Context $context): Span;

    /**
     * Returns the current {@see Span} from the current {@see Context},
     * falling back on {@see Span::getEmpty()} if there is no span in the current context.
     *
     * @todo Implement this in the API layer
     */
    public static function getCurrent(): Span;

    /**
     * Returns an invalid {@see Span} that is used when tracing is disabled, such s when there is no available SDK.
     */
    public static function getInvalid(): Span;

    /**
     * Returns a non-recording {@see Span} that hold the provided *$spanContext* but has no functionality.
     * It will not be exported and al tracing operations are no-op, but can be used to propagate a valid {@see SpanContext} downstream.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#wrapping-a-spancontext-in-a-span
     *
     * @todo Implement this in the API layer
     */
    public static function wrap(SpanContext $spanContext): Span;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#get-context
     */
    public function getContext(): SpanContext;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#isrecording
     */
    public function isRecording(): bool;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#set-attributes
     *
     * @param non-empty-string $key
     * @param bool|int|float|string|array|null $value Note: the array MUST be homogeneous, i.e. it MUST NOT contain values of different types.
     */
    public function setAttribute(string $key, $value): Span;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#set-attributes
     */
    public function setAttributes(Attributes $attributes): Span;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#add-events
     */
    public function addEvent(string $name, ?Attributes $attributes = null, int $timestamp = null): Span;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#record-exception
     */
    public function recordException(Throwable $exception, Attributes $attributes = null): Span;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#updatename
     *
     * @param non-empty-string $name
     */
    public function updateName(string $name): Span;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#set-status
     *
     * @psalm-param StatusCode::STATUS_* $code
     */
    public function setStatus(string $code, string $description = null): Span;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#end
     */
    public function end(int $endEpochNanos = null): void;
}
