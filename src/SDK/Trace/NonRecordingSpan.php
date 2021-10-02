<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Scope;
use Throwable;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#wrapping-a-spancontext-in-a-span
 *
 * @todo: Implement this on the API side.
 */
final class NonRecordingSpan implements API\SpanInterface
{
    private static ?self $invalidSpan = null;

    /** @inheritDoc */
    public static function fromContext(Context $context): API\SpanInterface
    {
        if ($span = $context->get(SpanContextKey::instance())) {
            return $span;
        }

        return self::getInvalid();
    }

    /** @inheritDoc */
    public static function getCurrent(): API\SpanInterface
    {
        return self::fromContext(Context::getCurrent());
    }

    /** @inheritDoc */
    public static function getInvalid(): API\SpanInterface
    {
        if (null === self::$invalidSpan) {
            self::$invalidSpan = new self(SpanContext::getInvalid());
        }

        return self::$invalidSpan;
    }

    /** @inheritDoc */
    public static function wrap(API\SpanContextInterface $spanContext): API\SpanInterface
    {
        return self::getInvalid();
    }

    private API\SpanContextInterface $context;

    public function __construct(
        API\SpanContextInterface $context
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
    public function getContext(): API\SpanContextInterface
    {
        return $this->context;
    }

    /** @inheritDoc */
    public function isRecording(): bool
    {
        return false;
    }

    /** @inheritDoc */
    public function setAttribute(string $key, $value): API\SpanInterface
    {
        return $this;
    }

    /** @inheritDoc */
    public function setAttributes(API\AttributesInterface $attributes): API\SpanInterface
    {
        return $this;
    }

    /** @inheritDoc */
    public function addEvent(string $name, ?API\AttributesInterface $attributes = null, int $timestamp = null): API\SpanInterface
    {
        return $this;
    }

    /** @inheritDoc */
    public function recordException(Throwable $exception, API\AttributesInterface $attributes = null): API\SpanInterface
    {
        return $this;
    }

    /** @inheritDoc */
    public function updateName(string $name): API\SpanInterface
    {
        return $this;
    }

    /** @inheritDoc */
    public function setStatus(string $code, string $description = null): API\SpanInterface
    {
        return $this;
    }

    /** @inheritDoc */
    public function end(int $endEpochNanos = null): void
    {
    }
}
