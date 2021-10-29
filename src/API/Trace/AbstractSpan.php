<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SDK\Trace\SpanContext;
use OpenTelemetry\SDK\Trace\SpanContextKey;

abstract class AbstractSpan implements SpanInterface
{
    private static ?self $invalidSpan = null;

    /** @inheritDoc */
    final public static function fromContext(Context $context): SpanInterface
    {
        if ($span = $context->get(SpanContextKey::instance())) {
            return $span;
        }

        return NonRecordingSpan::getInvalid();
    }

    /** @inheritDoc */
    final public static function getCurrent(): SpanInterface
    {
        return self::fromContext(Context::getCurrent());
    }

    /** @inheritDoc */
    final public static function getInvalid(): SpanInterface
    {
        if (null === self::$invalidSpan) {
            self::$invalidSpan = new NonRecordingSpan(SpanContext::getInvalid());
        }

        return self::$invalidSpan;
    }

    /** @inheritDoc */
    final public static function wrap(SpanContextInterface $spanContext): SpanInterface
    {
        if (!$spanContext->isValid()) {
            return self::getInvalid();
        }

        return new NonRecordingSpan($spanContext);
    }

    /** @inheritDoc */
    final public function activate(): ScopeInterface
    {
        return Context::getCurrent()->withContextValue($this)->activate();
    }

    /** @inheritDoc */
    final public function storeInContext(Context $context): Context
    {
        return $context->with(SpanContextKey::instance(), $this);
    }
}
