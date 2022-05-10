<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorage;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\Context\ScopeInterface;

abstract class AbstractSpan implements SpanInterface
{
    protected ContextStorageInterface $storage;

    /** @inheritDoc */
    final public static function fromContext(Context $context): SpanInterface
    {
        if ($span = $context->get(SpanContextKey::instance())) {
            return $span;
        }

        return (NonRecordingSpan::getInvalid())->setStorage($context->getStorage());
    }

    /** @inheritDoc */
    final public static function getCurrent(ContextStorageInterface $storage = null): SpanInterface
    {
        return self::fromContext($storage ? $storage->current() : ContextStorage::default()->current());
    }

    /** @inheritDoc */
    final public static function getInvalid(?ContextStorageInterface $storage = null): SpanInterface
    {
        return (new NonRecordingSpan(SpanContext::getInvalid()))->setStorage($storage ?? Context::defaultStorage());
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
        return $this->storage->current()->withContextValue($this)->activate();
    }

    /** @inheritDoc */
    final public function storeInContext(Context $context): Context
    {
        return $context->with(SpanContextKey::instance(), $this);
    }

    final public function setStorage(ContextStorageInterface $storage): SpanInterface
    {
        $this->storage = $storage;

        return $this;
    }
}
