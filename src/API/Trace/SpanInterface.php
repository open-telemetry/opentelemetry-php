<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ImplicitContextKeyedInterface;
use Throwable;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#span-operations
 */
interface SpanInterface extends ImplicitContextKeyedInterface
{
    /**
     * Returns the {@see SpanInterface} from the provided *$context*,
     * falling back on {@see SpanInterface::getInvalid()} if there is no span in the provided context.
     */
    public static function fromContext(ContextInterface $context): SpanInterface;

    /**
     * Returns the current {@see SpanInterface} from the current {@see ContextInterface},
     * falling back on {@see SpanInterface::getEmpty()} if there is no span in the current context.
     */
    public static function getCurrent(): SpanInterface;

    /**
     * Returns an invalid {@see SpanInterface} that is used when tracing is disabled, such s when there is no available SDK.
     */
    public static function getInvalid(): SpanInterface;

    /**
     * Returns a non-recording {@see SpanInterface} that hold the provided *$spanContext* but has no functionality.
     * It will not be exported and al tracing operations are no-op, but can be used to propagate a valid {@see SpanContext} downstream.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#wrapping-a-spancontext-in-a-span
     */
    public static function wrap(SpanContextInterface $spanContext): SpanInterface;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#get-context
     */
    public function getContext(): SpanContextInterface;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#isrecording
     */
    public function isRecording(): bool;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#set-attributes
     * Adding attributes at span creation is preferred to calling setAttribute later, as samplers can only consider information
     * already present during span creation
     * @param non-empty-string $key
     * @param bool|int|float|string|array|null $value Note: arrays MUST be homogeneous, i.e. it MUST NOT contain values of different types.
     */
    public function setAttribute(string $key, $value): SpanInterface;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#set-attributes
     * An attribute with a null key will be dropped, and an attribute with a null value will be dropped but also remove any existing
     * attribute with the same key.
     * @param iterable<non-empty-string, bool|int|float|string|array|null> $attributes
     */
    public function setAttributes(iterable $attributes): SpanInterface;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#add-events
     */
    public function addEvent(string $name, iterable $attributes = [], int $timestamp = null): SpanInterface;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#record-exception
     */
    public function recordException(Throwable $exception, iterable $attributes = []): SpanInterface;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#updatename
     *
     * @param non-empty-string $name
     */
    public function updateName(string $name): SpanInterface;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#set-status
     *
     * @psalm-param StatusCode::STATUS_* $code
     */
    public function setStatus(string $code, string $description = null): SpanInterface;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#end
     */
    public function end(int $endEpochNanos = null): void;
}
