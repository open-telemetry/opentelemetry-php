<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Scope;
use OpenTelemetry\Trace as API;
use Throwable;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#wrapping-a-spancontext-in-a-span
 *
 * @todo: Implement this on the API side.
 */
final class NonRecordingSpan implements API\Span
{
    private static ?self $invalidSpan = null;

    /** @inheritDoc */
    public static function fromContext(Context $context): API\Span
    {
        if ($span = $context->get(SpanContextKey::instance())) {
            return $span;
        }

        return self::getInvalid();
    }

    /** @inheritDoc */
    public static function getCurrent(): API\Span
    {
        return self::fromContext(Context::getCurrent());
    }

    /** @inheritDoc */
    public static function getInvalid(): API\Span
    {
        if (null === self::$invalidSpan) {
            self::$invalidSpan = new self(SpanContext::getInvalid());
        }

        return self::$invalidSpan;
    }

    /** @inheritDoc */
    public static function wrap(API\SpanContext $spanContext): API\Span
    {
        return self::getInvalid();
    }

    private API\SpanContext $context;

    public function __construct(
        API\SpanContext $context
    ) {
        $this->context = $context;
    }

    /** @inheritDoc */
    public function activate(): Scope
    {
        return Context::getCurrent()->withContextValue($this)->activate();
    }

    /** @inheritDoc */
    public function storeInContext(Context $context): Context
    {
        return $context->with(SpanContextKey::instance(), $this);
    }

    /** @inheritDoc */
    public function getContext(): API\SpanContext
    {
        return $this->context;
    }

    /** @inheritDoc */
    public function isRecording(): bool
    {
        return false;
    }

    /** @inheritDoc */
    public function setAttribute(string $key, $value): API\Span
    {
        return $this;
    }

    /** @inheritDoc */
    public function setAttributes(API\Attributes $attributes): API\Span
    {
        return $this;
    }

    /** @inheritDoc */
    public function addEvent(string $name, ?API\Attributes $attributes = null, int $timestamp = null): API\Span
    {
        return $this;
    }

    /** @inheritDoc */
    public function recordException(Throwable $exception, API\Attributes $attributes = null): API\Span
    {
        return $this;
    }

    /** @inheritDoc */
    public function updateName(string $name): API\Span
    {
        return $this;
    }

    /** @inheritDoc */
    public function setStatus(string $code, string $description = null): API\Span
    {
        return $this;
    }

    /** @inheritDoc */
    public function end(int $endEpochNanos = null): void
    {
    }
}
